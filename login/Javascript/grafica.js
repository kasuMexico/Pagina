  google.charts.load('current', {'packages':['corechart']});
  google.charts.setOnLoadCallback(init);

  function init(){
    const el = document.getElementById('curve_chart');

    function draw(){
      if(!el) return;

      const w = el.clientWidth || 320;               // ancho real del contenedor
      const h = Math.max(280, Math.round(w * 0.35)); // alto proporcional
      el.style.height = h + 'px';

      const options = {
        width:  w,
        height: h,
        legend: { position: 'bottom' },
        chartArea: {
          left: 40, right: 16, top: 24, bottom: 36,
          width: '100%', height: '100%'
        }
      };

      new google.visualization.LineChart(el).draw(data, options);
    }

    draw(); // primer render
    window.addEventListener('resize', debounce(draw,150));
  }

  // función debounce para no redibujar demasiado seguido
  function debounce(fn, ms){
    let t;
    return () => { clearTimeout(t); t = setTimeout(fn, ms); };
  }