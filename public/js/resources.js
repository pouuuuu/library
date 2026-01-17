let p;
let readMoreButton;

document.addEventListener("DOMContentLoaded", e =>  {
    p = document.querySelector(".synopsis > p, .description > p");
    readMoreButton = document.querySelector("#read-more");

    checkTruncation();

    readMoreButton.addEventListener("click", e => {
        p.classList.toggle("truncated")
        if (p.classList.contains("truncated")) {
            readMoreButton.textContent = "Afficher plus";
        } else {
            readMoreButton.textContent = "Afficher moins";
        }
    })
})

function checkTruncation() {
    if (p.scrollHeight > p.clientHeight) {
        readMoreButton.style.display = 'inline-block';
    } else {
        readMoreButton.style.display = 'none';
    }
}