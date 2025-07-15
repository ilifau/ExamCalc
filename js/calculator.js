(function () {
  const btn = document.getElementById('calculator-btn');
  const calc = document.getElementById('calculator');
  const display = document.getElementById('calc-display');
  const sciButtons = document.querySelectorAll('.sci');

  let current = '';
  let lastResult = null;
  let lastOperator = null;
  let lastOperand = null;

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
            let expression = current;

            if (expression === '' && lastResult !== null && lastOperator) {
              // Beispiel: Nur "+3" nach vorherigem Ergebnis
              expression = lastResult + lastOperator + lastOperand;
            }

            // Hier der Fix für die Wurzel: √ wird durch Math.sqrt ersetzt
            const parsed = expression
              .replace(/π/g, Math.PI)
              .replace(/e/g, Math.E)
              .replace(/√(\d+)/g, 'Math.sqrt($1)')  // Der reguläre Ausdruck für Wurzel
              .replace(/\^/g, '**')
              .replace(/sin/g, 'Math.sin')
              .replace(/cos/g, 'Math.cos')
              .replace(/tan/g, 'Math.tan')
              .replace(/log/g, 'Math.log10')
              .replace(/ln/g, 'Math.log');

            const result = eval(parsed);
            lastResult = result;
            display.value = result;
            current = '';
          } catch {
            current = 'Fehler';
            display.value = current;
          }
          break;

        case 'AC':
          current = '';
          lastResult = null;
          lastOperator = null;
          lastOperand = null;
          display.value = '';
          break;

        case 'SCI':
          sciButtons.forEach(b => {
            b.style.display = b.style.display === 'none' ? 'inline-block' : 'none';
          });
          return; // Nicht anzeigen

        default:
          // Speichere letzten Operator & Operand, wenn gültig
          if (/[\+\-\*\/]/.test(value)) {
            lastOperator = value;
            if (lastResult !== null) {
              current = lastResult.toString();
            }
          } else if (!isNaN(parseFloat(value)) || value === '.') {
            lastOperand = value;
          }

          current += value;
          display.value = current;
      }
    });
  });
})();
