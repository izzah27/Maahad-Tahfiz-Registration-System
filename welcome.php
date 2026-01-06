<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Arial:wght@700&display=swap" />
    <title>Welcome Page</title>
</head>
<style>
*{
    padding: 0;
    margin: 0;
    box-sizing: border-box;
}
body{
    width: 100%;
    background-color: #E2E0E0;
    background-size: cover;
    display: flex; 
    align-items: center; 
    flex-direction: column;
}
h1{
    font-family: 'Ibarra Real Nova';
    font-size: 60px;
    margin-top: 20px;
    line-height: 1.5;
    font-weight: 200;
}
.line-border{
    width: 55%;
    border: 1px solid;
}
p{
    margin-top: 30px;
    font-family: 'Inter';
    font-size: 20px;
}
.wrapper{
    margin-top: 40px;
    border: 2px solid;
    height: 350px;
    width: 502px;
    background-color: white;
    border: none;
    border-radius: 30px;
}
.wrapper p{
    text-align: center;
    font-size: 60px;
    font-family: 'Ibarra Real Nova';
    margin-top: 70px;
}
.wrapper a{
    border: 2px solid;
    align-items: center;
    justify-content: center;
    display: flex;
    color: black;
    font-family: 'Inter';
    font-size: 25px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 100;
    width: 80%;
    padding: 20px;
}
.wrapper .button1{
    margin-top: 25px;
    background-color: #D09B35;
    border: none;
    margin-left: 50px;
    transition: background-color 0.3s, color 0.3s;

}
.wrapper .button1:hover{
    background-color: #b0832b;
    color: white;
}
.img-class img{
    width: 150px;
    height: 150px;
    margin-top: 50px;
}
</style>
<body>
    <div class="img-class">
        <img src="logo.png" alt="">
    </div>
    
    <h1>WELCOME TO MAAHAD SAINS TOK GURU</h1>
    <div class="line-border"></div>
    <p>Melahirkan pelajar yang berprestasi tinggi dan mampu memimpin ummah berdasarkan al-Quran dan as-Sunnah</p>
    <div class="wrapper">
        <p>Hello!</p><br>
        <a href="login.php" class="button1">LOGIN</a>
    </div>
</body>
</html>