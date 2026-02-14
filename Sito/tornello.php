<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Tornello Mensa</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
    </style>
</head>
<body>
    <div class="tornello-container">
        <h1 class="text-dark mb-4">Simulazione del Tornello</h1>
        
        <div id="scannerArea">
            <button class="btn btn-primary btn-lg mb-3" onclick="avviaScanner()">
                Avvia Scanner
            </button>
            <div id="reader" style="width:100%; max-width:400px;"></div>
        </div>
        
        <div id="risultato" class="result-box">
            <h2 id="resultTitle"></h2>
            <p id="resultMessage" class="fs-4"></p>
            <div id="resultDetails" class="mt-3"></div>
        </div>
        
        <button id="btnReset" class="btn btn-secondary btn-lg mt-4" style="display:none;" onclick="resetScanner()">
            Nuova Scansione
        </button>
    </div>
    
    <script>
        let scanner;
        
        function avviaScanner() {
            document.getElementById('risultato').style.display = 'none';
            document.getElementById('btnReset').style.display = 'none';
            
            scanner = new Html5QrcodeScanner("reader", { 
                fps: 10, 
                qrbox: 250 
            });
            
            scanner.render(onScanSuccess);
        }
        
        function onScanSuccess(decodedText) {
            // Ferma lo scanner
            scanner.clear();
            
            // Mostra caricamento
            mostraRisultato('loading', 'Verifica in corso...', '', '');
            
            // Invia il token al server
            fetch('procedure/valida_token.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ token: decodedText })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostraRisultato(
                        'success',
                        'ACCESSO CONSENTITO',
                        `${data.studente} - ${data.tipo_pasto}`,
                        ''
                    );
                } else {
                    mostraRisultato(
                        'error',
                        'ACCESSO NON CONSENTITO',
                        data.message,
                        ''
                    );
                   
                }
            })
            .catch(error => {
                console.error('Errore:', error);
                mostraRisultato(
                    'error',
                    'ERRORE',
                    'Errore di connessione',
                    ''
                );
            });
        }
        
        function mostraRisultato(tipo, titolo, messaggio, dettagli) {
            const box = document.getElementById('risultato');
            const title = document.getElementById('resultTitle');
            const msg = document.getElementById('resultMessage');
            const details = document.getElementById('resultDetails');

            box.className = 'result-box';

            if (tipo === 'success') {
                title.className = 'text-success';
            } else if (tipo === 'error') {
                title.className = 'text-danger';
            } else {
                title.className = '';
            }
            
            title.textContent = titolo;
            msg.textContent = messaggio;
            details.innerHTML = dettagli;
            
            box.style.display = 'block';
            document.getElementById('btnReset').style.display = 'inline-block';
            
            // Reset automatico dopo 5 secondi
            if (tipo !== 'loading') {
                setTimeout(resetScanner, 5000);
            }
        }
        
        function resetScanner() {
            document.getElementById('risultato').style.display = 'none';
            document.getElementById('btnReset').style.display = 'none';
            avviaScanner();
        } 
        
        // Avvia automaticamente lo scanner
        window.onload = avviaScanner;
    </script>
</body>
</html>