<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div style="border: 3px solid black;">
        <h2>Register</h2>
        <from action="/register" method="post">
            @csrf
            <input type="text" placehoder="name">
            <input type="text" placehoder="email">
            <input type="password" placehoder="password">
            <button>Register</button>
        </from>
    </div>
</body>
</html>