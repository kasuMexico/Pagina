// SPDX-License-Identifier: MIT
pragma solidity ^0.8.26;

import "@openzeppelin/contracts/token/ERC721/ERC721.sol";
import "@openzeppelin/contracts/token/ERC721/extensions/ERC721Royalty.sol";
import "@openzeppelin/contracts/access/AccessControl.sol";
import "@openzeppelin/contracts/utils/Pausable.sol";
import "@openzeppelin/contracts/utils/ReentrancyGuard.sol";
import "@openzeppelin/contracts/utils/cryptography/ECDSA.sol";
import "@openzeppelin/contracts/utils/cryptography/EIP712.sol";
import "@openzeppelin/contracts/utils/Strings.sol";

/**
 * @title KasuPolicyShare
 * @notice Contrato inteligente para la tokenización de pólizas RWA vitalicias de KASU.
 * @dev Implementa ERC-721 con EIP-2981 (Royalties al 5%), EIP-712 (Mint con firma) y control de roles.
 */
contract KasuPolicyShare is 
    ERC721, 
    ERC721Royalty, 
    AccessControl, 
    Pausable, 
    ReentrancyGuard, 
    EIP712 
{
    using Strings for uint256;

    // --- ROLES ---
    bytes32 public constant MINTER_ROLE  = keccak256("MINTER_ROLE");
    bytes32 public constant PAUSER_ROLE  = keccak256("PAUSER_ROLE");
    bytes32 public constant UPDATER_ROLE = keccak256("UPDATER_ROLE");

    // --- CONSTANTES ---
    uint96 public constant ROYALTY_BPS = 500; // 500 BPS = 5.00%
    bytes32 private constant _PERMIT_TYPEHASH = 
        keccak256("Permit(address owner,uint256 tokenId,uint256 nonce,uint256 deadline)");

    // --- ENUMS & STRUCTS ---
    enum PolicyStatus { Active, Claimed, Revoked, Resolved }

    struct PolicyMetadata {
        string policyType;      // Ej. "Servicio Funerario"
        string clientName;      // Hash/Ofuscado para privacidad
        string clientDocument;  // Hash/Ofuscado para privacidad
        string vehicleInfo;     // Vacío para servicios funerarios
        string additionalData;  // JSON auxiliar ej. {"id_venta": 1024}
    }

    struct PolicyInfo {
        bytes32 idHash;         // keccak256(idFirma)
        string description;     // Descripción legible de la póliza
        uint256 premiumAmount;  // Monto prima (Informativo, ej. 3500)
        uint256 coverageAmount; // Monto cobertura (Informativo, ej. 100000)
        uint256 liquidationDate;// Unix Timestamp de la liquidación (inicio de rendimiento)
        PolicyStatus status;    // Estado de la póliza (0 = Active, 1 = Claimed, etc.)
        bool isActive;          // Flag de actividad vitalicia
        PolicyMetadata metadata;// Datos complementarios del cliente
    }

    // --- VARIABLES DE ESTADO ---
    string private _baseTokenURI;
    address public treasuryWallet;
    uint256 public totalMinted;

    mapping(uint256 => PolicyInfo) public policies;
    mapping(bytes32 => uint256) public idFirmaToToken;
    mapping(uint256 => bytes32) public tokenToIdFirmaHash;
    mapping(bytes32 => string) private _idFirmaData;
    mapping(address => uint256) public nonces;

    // --- EVENTOS ---
    event PolicyMinted(
        uint256 indexed tokenId, 
        string idFirma, 
        address indexed owner, 
        uint256 premium, 
        uint256 coverage,
        uint256 liquidationDate
    );
    event PolicyStatusUpdated(uint256 indexed tokenId, PolicyStatus newStatus);
    event BaseURIUpdated(string newBaseURI);
    event TreasuryWalletUpdated(address newTreasury);

    // --- CONSTRUCTOR ---
    constructor(
        address defaultAdmin,
        address minter,
        address _treasuryWallet,
        string memory baseURI
    ) 
        ERC721("KasuPolicyShare", "KASU") 
        EIP712("KasuPolicyShare", "2.0.0") 
    {
        require(defaultAdmin != address(0), "Admin invalido");
        require(_treasuryWallet != address(0), "Treasury invalida");

        _grantRole(DEFAULT_ADMIN_ROLE, defaultAdmin);
        _grantRole(MINTER_ROLE, minter);
        _grantRole(PAUSER_ROLE, defaultAdmin);
        _grantRole(UPDATER_ROLE, defaultAdmin);

        treasuryWallet = _treasuryWallet;
        _baseTokenURI = baseURI;

        // Configuración de Royalty al 5% según EIP-2981
        _setDefaultRoyalty(_treasuryWallet, ROYALTY_BPS);
    }

    // --- FUNCIONES DE MINTEO ---

    /**
     * @notice Minteo directo realizado por el Tesoro u Oráculo backend (requiere MINTER_ROLE).
     */
    function mintToTreasury(
        uint256 tokenId,
        string calldata idFirma,
        PolicyMetadata calldata metadata,
        uint256 premiumAmount,
        uint256 coverageAmount,
        uint256 liquidationDate
    ) external onlyRole(MINTER_ROLE) whenNotPaused nonReentrant returns (uint256) {
        return _processMint(treasuryWallet, tokenId, idFirma, metadata, premiumAmount, coverageAmount, liquidationDate);
    }

    /**
     * @notice Minteo mediante firma tipada EIP-712 (Permit pattern para usuarios finales).
     */
    function mintWithSignature(
        uint256 tokenId,
        string calldata idFirma,
        PolicyMetadata calldata metadata,
        uint256 premiumAmount,
        uint256 coverageAmount,
        uint256 liquidationDate,
        uint256 deadline,
        bytes memory signature
    ) external whenNotPaused nonReentrant returns (uint256) {
        require(block.timestamp <= deadline, "Firma expirada");

        bytes32 structHash = keccak256(
            abi.encode(_PERMIT_TYPEHASH, msg.sender, tokenId, nonces[msg.sender]++, deadline)
        );
        bytes32 digest = _hashTypedDataV4(structHash);
        address signer = ECDSA.recover(digest, signature);

        require(hasRole(MINTER_ROLE, signer), "Firma no autorizada por Oraculo");

        return _processMint(msg.sender, tokenId, idFirma, metadata, premiumAmount, coverageAmount, liquidationDate);
    }

    // --- FUNCIÓN INTERNA DE MINTEO ---
    function _processMint(
        address to,
        uint256 tokenId,
        string calldata idFirma,
        PolicyMetadata calldata metadata,
        uint256 premiumAmount,
        uint256 coverageAmount,
        uint256 liquidationDate
    ) internal returns (uint256) {
        require(bytes(idFirma).length > 0, "idFirma vacio");
        require(liquidationDate <= block.timestamp, "Fecha de liquidacion no puede ser futura");

        bytes32 idHash = keccak256(abi.encodePacked(idFirma));
        require(idFirmaToToken[idHash] == 0, "idFirma ya tokenizado");

        _safeMint(to, tokenId);

        policies[tokenId] = PolicyInfo({
            idHash: idHash,
            description: string(abi.encodePacked("Poliza Vitalicia KASU #", idFirma)),
            premiumAmount: premiumAmount,
            coverageAmount: coverageAmount,
            liquidationDate: liquidationDate,
            status: PolicyStatus.Active,
            isActive: true,
            metadata: metadata
        });

        idFirmaToToken[idHash] = tokenId;
        tokenToIdFirmaHash[tokenId] = idHash;
        _idFirmaData[idHash] = idFirma;
        
        totalMinted++;

        emit PolicyMinted(tokenId, idFirma, to, premiumAmount, coverageAmount, liquidationDate);
        return tokenId;
    }

    // --- GESTIÓN DE ESTADOS ---

    /**
     * @notice Actualiza el estado de una póliza (ej. cambiar a Claimed/Ejecutada por siniestro o Revoked).
     */
    function updatePolicyStatus(uint256 tokenId, PolicyStatus newStatus) external onlyRole(UPDATER_ROLE) {
        _requireOwned(tokenId);
        
        PolicyInfo storage policy = policies[tokenId];
        policy.status = newStatus;
        
        if (newStatus != PolicyStatus.Active) {
            policy.isActive = false;
        }

        emit PolicyStatusUpdated(tokenId, newStatus);
    }

    // --- CONSULTAS / VIEW FUNCTIONS ---

    /**
     * @notice Devuelve el tokenURI apuntando a la API PHP (`metadata.php?id={idFirma}`).
     */
    function tokenURI(uint256 tokenId) public view override returns (string memory) {
        _requireOwned(tokenId);
        bytes32 hash = tokenToIdFirmaHash[tokenId];
        string memory idFirma = _idFirmaData[hash];
        return string(abi.encodePacked(_baseTokenURI, idFirma));
    }

    /**
     * @notice Obtiene la información completa de una póliza.
     */
    function getPolicyInfo(uint256 tokenId) external view returns (PolicyInfo memory) {
        _requireOwned(tokenId);
        return policies[tokenId];
    }

    /**
     * @notice Verifica si una póliza está activa (Vitalicia: activa mientras conserve el estado Active).
     */
    function isPolicyActive(uint256 tokenId) external view returns (bool) {
        PolicyInfo memory info = policies[tokenId];
        return info.isActive && info.status == PolicyStatus.Active;
    }

    // --- ADMINISTRACIÓN ---

    function setBaseURI(string memory newBaseURI) external onlyRole(DEFAULT_ADMIN_ROLE) {
        _baseTokenURI = newBaseURI;
        emit BaseURIUpdated(newBaseURI);
    }

    function setTreasuryWallet(address newTreasury) external onlyRole(DEFAULT_ADMIN_ROLE) {
        require(newTreasury != address(0), "Direccion invalida");
        treasuryWallet = newTreasury;
        _setDefaultRoyalty(newTreasury, ROYALTY_BPS);
        emit TreasuryWalletUpdated(newTreasury);
    }

    function pause() external onlyRole(PAUSER_ROLE) {
        _pause();
    }

    function unpause() external onlyRole(PAUSER_ROLE) {
        _unpause();
    }

    // --- OVERRIDES REQUERIDOS POR SOLIDITY / OPENZEPPELIN ---

    function supportsInterface(bytes4 interfaceId)
        public
        view
        override(ERC721, ERC721Royalty, AccessControl)
        returns (bool)
    {
        return super.supportsInterface(interfaceId);
    }
}