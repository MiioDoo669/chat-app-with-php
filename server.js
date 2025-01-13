const WebSocket = require('ws'); // Import the WebSocket library
const wss = new WebSocket.Server({ port: 8080 }); // Create the WebSocket server on port 8080

let clients = [];

wss.on('connection', (ws) => {
    // Store the client info
    ws.on('message', (message) => {
        const data = JSON.parse(message);  // Parse the incoming message

        if (data.type === 'join') {
            ws.username = data.username;  // Store the username
            broadcastClientCount();  // Broadcast client count after a new user joins
        } else if (data.type === 'message') {
            // Broadcast the message to all clients
            broadcastMessage(data);
        }
    });

    ws.on('close', () => {
        // Remove client from the list on disconnect
        clients = clients.filter(client => client !== ws);
        broadcastClientCount();  // Send updated client count
    });

    clients.push(ws);  // Add the new client to the list
    broadcastClientCount();  // Send the total number of clients to all connected clients
});

// Broadcast the number of connected clients to all clients
function broadcastClientCount() {
    const countMessage = JSON.stringify({
        type: 'clientCount',
        count: clients.length
    });
    clients.forEach(client => client.send(countMessage));
}

// Broadcast a message to all clients
function broadcastMessage(data) {
    const message = JSON.stringify({
        type: 'message',
        sender: data.sender,
        message: data.message
    });
    clients.forEach(client => client.send(message));
}

console.log('WebSocket server is running on ws://localhost:8080');
