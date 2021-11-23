function randomColor (){
    return '#'+Math.floor(Math.random()*16777215).toString(16)
}

const gradientButton = document.getElementById("gradientButton")

gradientButton.addEventListener("click", ()=>{
    let color1 = randomColor()
    let color2 = randomColor()
    document.body.style.background = `linear-gradient(to right, ${color1}, ${color2})`
})