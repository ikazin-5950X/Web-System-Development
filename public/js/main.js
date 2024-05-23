// HTMLのクラス名に基づいて要素を取得します
const cls1Elements = document.getElementsByClassName('cls-1');
const cls2Elements = document.getElementsByClassName('cls-2');

// クリックされたときに呼び出される関数を定義します
function changeColorOnClick(event) {
    // ランダムなRGB値を生成します
    const randomColor = '#' + Math.floor(Math.random()*16777215).toString(16);
    
    // クリックされた要素のクラスによって色を変更します
    if (event.target.classList.contains('cls-1')) {
        for (let i = 0; i < cls1Elements.length; i++) {
            cls1Elements[i].style.fill = randomColor;
        }
    } else if (event.target.classList.contains('cls-2')) {
        for (let i = 0; i < cls2Elements.length; i++) {
            cls2Elements[i].style.fill = randomColor;
        }
    }
}

// .cls-1と.cls-2の要素がクリックされたらchangeColorOnClick関数を呼び出します
for (let i = 0; i < cls1Elements.length; i++) {
    cls1Elements[i].addEventListener('click', changeColorOnClick);
}

for (let i = 0; i < cls2Elements.length; i++) {
    cls2Elements[i].addEventListener('click', changeColorOnClick);
}
