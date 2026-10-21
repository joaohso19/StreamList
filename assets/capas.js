async function buscaTMDB(tipo, nome) {
    const resp = await fetch('/streamlist/app/tmdb.php?tipo=' + tipo + '&q=' + encodeURIComponent(nome));
    if (!resp.ok) return [];
    return await resp.json();
}

const buscaFilmes = nome => buscaTMDB('movie', nome);
const buscaSeries = nome => buscaTMDB('tv', nome);

function escolheCapa(url) {
    document.getElementById('capa').value = url;

    const preview = document.getElementById('preview-capa');
    preview.src = url;
    preview.style.display = 'block';
    document.getElementById('sem-capa').style.display = 'none';

    document.querySelectorAll('.capa-opcao').forEach(function (img) {
        img.classList.toggle('selecionada', img.src === url);
    });
}