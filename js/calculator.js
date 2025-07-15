(function () {
  const btn = document.getElementById('calculator-btn');
  const calc = document.getElementById('calculator');
  const display = document.getElementById('calc-display');
  const sciButtons = document.querySelectorAll('.sci');
  let current = '';

  if (!btn || !calc || !display) {
    console.error("Taschenrechner-Elemente nicht gefunden.");
    return;
  }

  // Anzeigen / Verstecken
  btn.addEventListener('click', () => {
    calc.style.display = (calc.style.display === 'none' || !calc.style.display) ? 'block' : 'none';
  });

  // Shortcut Alt + R
  document.addEventListener('keydown', function (e) {
    if (e.altKey && e.key.toLowerCase() === 'r') {
      calc.style.display = (calc.style.display === 'none' || !calc.style.display) ? 'block' : 'none';
    }
  });

  // Klicks verarbeiten
  calc.querySelectorAll('button').forEach(button => {
    button.addEventListener('click', () => {
      const value = button.textContent;

      switch (value) {
        case '=':
          try {
            const parsed = current
              .replace(/π/g, Math.PI)
              .replace(/e/g, Math.E)
              .replace(/√/g, 'Math.sqrt')
              .replace(/\^/g, '**')
              .replace(/sin/g, 'Math.sin')
              .replace(/cos/g, 'Math.cos')
              .replace(/tan/g, 'Math.tan')
              .replace(/log/g, 'Math.log10')
              .replace(/ln/g, 'Math.log');
            current = eval(parsed).toString();
          } catch {
            current = 'Fehler';
          }
          break;
        case 'AC':
          current = '';
          break;
        case 'SCI':
          sciButtons.forEach(b => {
            b.style.display = b.style.display === 'none' ? 'inline-block' : 'none';
          });
          return; // nicht anzeigen
        default:
          current += value;
      }

      display.value = current;
      if (value === '=') current = '';
    });
  });
})();
