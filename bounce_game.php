<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<style>
canvas {
    border:4px solid red;
	background: #00ff00 url("gras.jpg") repeat ;
}
</style>
</head>
<div id="myfilter" style="position:absolute;background-color:#fe1a00;opacity:0.3;width:480px;height:272px;display:none"></div>
<div id="myrestartbutton" style="position:absolute;padding-top:100px;padding-left:160px;display:none;"><button onclick="restartGame()">Restart</button></div>
<div id="canvascontainer"></div>
<br>
<button style="margin-left:200px; height:60px;width:100px; background-color:green;" onmousedown="accelerate(-0.5)" onmouseup="accelerate(0.09)">Start</button>
<br>

<script>

var myGameArea;
var myGamePiece;
var myObstacles = [];
var myscore;
var mySound;
var myMusic;

function restartGame() {
document.getElementById("myfilter").style.display = "none";
document.getElementById("myrestartbutton").style.display = "none";
myGameArea.stop();
myGameArea.clear();
myGameArea = {};
myGamePiece = {};
myObstacles = [];
myscore = {};
document.getElementById("canvascontainer").innerHTML = "";
startGame()
}

function startGame() {
    myGameArea = new gamearea();
    myGamePiece = new component(40, 40, "tenis.gif", 10, 120, "image");
    myscore = new component("15px", "Consolas", "white", 220, 25, "text");
	mySound = new sound("music.mp3");
    myMusic = new sound("tenis_music.mp3");
	myGamePiece.gravity = 0.05;
    myMusic.play();
    myGameArea.start();
}

function gamearea() {
    this.canvas = document.createElement("canvas");
    this.canvas.width = 480;
    this.canvas.height = 270;    
    document.getElementById("canvascontainer").appendChild(this.canvas);
    this.context = this.canvas.getContext("2d");
    this.pause = false;
    this.frameNo = 0;
    this.start = function() {
        this.interval = setInterval(updateGameArea, 20);
    }
    this.stop = function() {
        clearInterval(this.interval);
        this.pause = true;
    }
    this.clear = function(){
        this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
    }
}

function component(width, height, color, x, y, type) {

    this.type = type;
    if (type == "text") {
        this.text = color;
    }
	if (type == "image" || type == "background") {
        this.image = new Image();
        this.image.src = color;
    }
    this.score = 0;
    this.width = width;
    this.height = height;
    this.speedX = 0;
    this.speedY = 0;    
    this.x = x;
    this.y = y;
    this.gravity = 0;
    this.gravitySpeed = 0;
	this.bounce = 0.6;
	
    this.update = function() {
        ctx = myGameArea.context;
        if (this.type == "text") {
            ctx.font = this.width + " " + this.height;
            ctx.fillStyle = color;
            ctx.fillText(this.text, this.x, this.y);
        }if (type == "image" || type == "background") {
            ctx.drawImage(this.image,this.x, this.y,this.width, this.height);
        if (type == "background") {
            ctx.drawImage(this.image,  this.x + this.width, this.y,this.width, this.height);
        } 
		} else {
            ctx.fillStyle = color;
            ctx.fillRect(this.x, this.y, this.width, this.height);
        }
    }
	this.newPos = function() {
        this.gravitySpeed += this.gravity;
        this.x += this.speedX;
        this.y += this.speedY + this.gravitySpeed;
        this.hitBottom();
    }
    this.hitBottom = function() {
        var rockbottom = myGameArea.canvas.height - this.height;
        if (this.y > rockbottom) {
            this.y = rockbottom;
            this.gravitySpeed = -(this.gravitySpeed * this.bounce);
        }
		var rockbottom =0;
		if(this.y<0){
			this.y=rockbottom;
			this.gravitySpeed = -(this.gravitySpeed * this.bounce);
		}
    }
    this.crashWith = function(otherobj) {
        var myleft = this.x;
        var myright = this.x + (this.width);
        var mytop = this.y;
        var mybottom = this.y + (this.height);
        var otherleft = otherobj.x;
        var otherright = otherobj.x + (otherobj.width);
        var othertop = otherobj.y;
        var otherbottom = otherobj.y + (otherobj.height);
        var crash = true;
        if ((mybottom < othertop) || (mytop > otherbottom) || (myright < otherleft) || (myleft > otherright)) {
            crash = false;
        }
        return crash;
    }
}

function updateGameArea() {
    var x, y, min, max, height, gap;
    for (i = 0; i < myObstacles.length; i += 1) {
        if (myGamePiece.crashWith(myObstacles[i])) {
            myGameArea.stop();
			mySound.play();
            myMusic.stop();
            myGameArea.stop();
            document.getElementById("myfilter").style.display = "block";
            document.getElementById("myrestartbutton").style.display = "block";
            return;
        } 
    }
    if (myGameArea.pause == false) {
        myGameArea.clear();
        myGameArea.frameNo += 1;
        myscore.score +=1;        
        if (myGameArea.frameNo == 1 || everyinterval(150)) {
            x = myGameArea.canvas.width;
            y = myGameArea.canvas.height - 100;
            min = 20;
            max = 100;
            height = Math.floor(Math.random()*(max-min+1)+min);
            min = 50;
            max = 100;
            gap = Math.floor(Math.random()*(max-min+1)+min);
            myObstacles.push(new component(17, height, "stamp.gif", x, 0,"image"));
			myObstacles.push(new component(17, x - height - gap, "stamp1.gif", x, height + gap,"image"));
        }
        for (i = 0; i < myObstacles.length; i += 1) {
            myObstacles[i].x += -1;
            myObstacles[i].update();
        }
        myscore.text="SCORE: " + Math.ceil((myGameArea.frameNo)/250);    
        myscore.update();
		myGamePiece.newPos();
        myGamePiece.update();
    }
}

function sound(src) {
    this.sound = document.createElement("audio");
    this.sound.src = src;
    this.sound.setAttribute("preload", "auto");
    this.sound.setAttribute("controls", "none");
    this.sound.style.display = "none";
    document.body.appendChild(this.sound);
    this.play = function(){
        this.sound.play();
    }
    this.stop = function(){
        this.sound.pause();
    }    
}

function everyinterval(n) {
    if ((myGameArea.frameNo / n) % 1 == 0) {return true;}
    return false;
}
function accelerate(n) {
    if (!myGameArea.interval) {myGameArea.interval = setInterval(updateGameArea, 20);}
    myGamePiece.gravity = n;
}

function clearmove(e) {
    myGamePiece.speedX = 0; 
    myGamePiece.speedY = 0; 
}
startGame();

</script>
</body>
</html>
