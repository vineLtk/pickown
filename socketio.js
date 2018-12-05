var app = require('http').createServer(handler);
var io = require('socket.io')(app);

var Redis = require('ioredis');
        var redis = new Redis();

        app.listen(6001, function () {
            console.log('Server is running!') ;
        });

        function handler(req, res) {
            res.writeHead(200);
            res.end('');
        }

        io.on('connection', function (socket) {
            socket.on('message', function (message) {
        console.log(message)
    })
    socket.on('disconnect', function () {
        console.log('user disconnect')
    })
});


redis.psubscribe('*');

redis.on('pmessage', function (subscrbed, channel, message) {
    message = JSON.parse(message);
    io.emit(message.event, message.data);
});