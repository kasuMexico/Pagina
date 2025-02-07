function OcuForCurp(e){
    "RegCurBen"==e.value?(
        divC=document.getElementById("RegCurBen"),
        divC.style.display="",
        divT=document.getElementById("RegCurCli"),
        divT.style.display="none"):(
        divC=document.getElementById("RegCurBen"),
        divC.style.display="none",
        divT=document.getElementById("RegCurCli"),
        divT.style.display=""
    )}
//Funcion que calcula el precio de una poliza
    function CalPre(e){
            IntB100=parseFloat(IntPhp),
            Cost=parseInt(CostPhp),
            TaInAn=IntB100/100,
            TaInMen=TaInAn/12,
            "0"==e.value?(//Bajamos la tasa a porcentaje
            Np="",
            c=Cost,
            d=Cost,
            Vpag="1 solo Pago de"):(
            Vpag="Meses de",
            Tiempo=e.value,
            BaseA=TaInMen+1,//Sumamos 1 a la tasa de interes
            BaseB=Math.pow(BaseA,Tiempo),//Sacamos la tasa total a pagar en el periodo
            TaReXi=BaseB*TaInMen,//Multiplicamos la tasa total por el periodo
            CuaVar=TaReXi*Cost,//Multiplicamos la tasa real por el periodo por la cantidad
            ValRes=BaseB-1,//Restamos a valor elevado a la potencia 1
            BaseD=CuaVar/ValRes,//Unimos los dos valores para obtener el pago
            BaseC=BaseD*Tiempo,//Obtenemos el total a pagar
            Np=Tiempo,
            c=BaseC.toFixed(2),
            d=BaseD.toFixed(2)),
            document.getElementById("PagosCosto").innerHTML="\
                <h3 class='derTit'>"+Np+" "+Vpag+" </h3> \
                <h3 class='IzqTit'>$ "+d+"</h3> \
                <p class='derTit'>Total a pagar</p> \
                <h4 class='IzqTit'>$ "+c+" Mxn</h4> \
                <input type='text' name='Subtotal' value="+c+" style='display: none;'> \
                <input type='text' name='Pagos' value="+d+" style='display: none;'> \
                "
        }
//Validamos que se haya seleccionado un producto
function validate(e){
    for(var t=document.getElementById("form"),
        n=!1,a=0;a<t.Producto.length;a++)
        t.Producto[a].checked&&(n=!0);n||(
            alert("debes seleccionar un Servicio para poder continuar"),e.preventDefault?e.preventDefault():e.returnValue=!1
        )
}
