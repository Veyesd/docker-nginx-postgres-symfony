const express = require('express');

const app = express();

app.get('/', function(req, res) {

    res.send("Bonjour, j'utilise NODEJS.");

});


app.listen(8887);