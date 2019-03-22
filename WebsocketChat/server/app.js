// configuration
var config = require('./config');

// application
var http = require('http');
var app = http.createServer();

// socket.io
var io = require('socket.io').listen(app);

io.set('transports', ['websocket', 'xhr-polling', 'jsonp-polling', 'polling']);

// callbacks
io.sockets.on('connection', function(socket) {
    
    var currentRoomId = socket.id;
    
    // send list of rooms
    socket.emit('room-list', config.rooms);
    
    // join to room
    socket.on('room-join', function(roomId) {
        socket.leave(currentRoomId);
        socket.join(roomId);
        currentRoomId = roomId;
        
        // send user joined to all sockets in room
        io.sockets
            .to(currentRoomId)
            .emit('room-joined', {
                login: socket.id
            });
    });
    
    // listen for client messages
    socket.on('send-message', function(message, d) {
        io.sockets
            .to(socket.rooms[0])
            .emit('receive-message', '<b>' + socket.id + '</b> ' + message);
    });
    
    
    // disconnect
    socket.on('disconnect', function() {});
    
    // handle errors
    socket.on('error', function(reason) {
        console.log('Server error: ' + reason);
    });
});


// handle signals
var exitHandler = function(code) {
    code = code || 0;
    process.exit(code);
};

process.on('SIGINT', exitHandler); // Ctrl+C
process.on('SIGTERM', exitHandler); // kill signal
    
// start server
app.listen(config.port, function() {
    console.log('Starting server at port ' + config.port);
});