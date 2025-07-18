(function () {
  const debugBox = document.createElement('div');
  debugBox.style.position = 'fixed';
  debugBox.style.bottom = '10px';
  debugBox.style.left = '10px';
  debugBox.style.background = 'rgba(0,0,0,0.8)';
  debugBox.style.color = 'lime';
  debugBox.style.padding = '10px';
  debugBox.style.zIndex = '100000';
  debugBox.style.fontFamily = 'monospace';
  debugBox.style.fontSize = '14px';
  debugBox.style.maxWidth = '400px';
  debugBox.style.whiteSpace = 'pre-wrap';
  debugBox.innerText = '[ExamCalc Debug] wird geladen...';
  document.body.appendChild(debugBox);

  const log = (msg) => {
    debugBox.innerText += '\n' + msg;
    console.log('[ExamCalc]', msg);
  };

  window.examcalc_debug = log;

  // Führe jetzt deinen originalen Code weiter unten aus
  const insertCalculator = async () => {
    try {
      log('🧠 examcalc.js gestartet');
      const response = await fetch('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/templates/calculator_overlay.html');
      log('✅ calculator_overlay.html geladen');

      const html = await response.text();
      const wrapper = document.createElement('div');
      wrapper.innerHTML = html;
      document.body.appendChild(wrapper);
      log('✅ Overlay eingefügt');

      const script = document.createElement('script');
      script.src = './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/js/calculator.js';
      document.body.appendChild(script);
      log('✅ calculator.js eingebunden');

    } catch (error) {
      log('❌ Fehler beim Laden: ' + error.message);
    }
  };

  window.addEventListener('load', insertCalculator);
})();
