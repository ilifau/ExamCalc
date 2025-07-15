(function () {
  console.log("📦 examcalc.js wurde geladen");

  const insertCalculator = async () => {
    try {
      const response = await fetch('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/templates/calculator_overlay.html');
      const html = await response.text();
      const wrapper = document.createElement('div');
      wrapper.innerHTML = html;
      document.body.appendChild(wrapper);

      const script = document.createElement('script');
      script.src = './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ExamCalc/js/calculator.js';
      document.body.appendChild(script);
    } catch (error) {
      console.error('❌ Rechner konnte nicht geladen werden:', error);
    }
  };

  window.addEventListener('load', insertCalculator);
})();
