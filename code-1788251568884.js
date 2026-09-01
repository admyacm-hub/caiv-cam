document.getElementById('formDenuncia').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('enviar_denuncia.php', {
            method: 'POST',
            body: formData
        });
        const resultado = await response.json();
        
        if (resultado.sucesso) {
            alert(`Denúncia enviada com sucesso! Código de Protocolo: ${resultado.protocolo}`);
            this.reset();
        } else {
            alert(`Aviso: ${resultado.mensagem}`);
        }
    } catch (error) {
        console.error('Erro na requisição:', error);
        alert('Ocorreu um erro ao comunicar com o servidor.');
    }
});