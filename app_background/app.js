require("dotenv").config();
const cron = require("node-cron");

cron.schedule("0 1 * * *", async () => {
    try {
        const response = await fetch(
            `${process.env.BASE_URL}/autoclaimingvoucher`
        );
        const response1 = await fetch(`${process.env.BASE_URL}/schedule`);
        const responseJson = await response.json();
        const response1Json = await response1.json();
        console.log(responseJson);
        console.log(response1Json);
    } catch (error) {
        console.log(error);
    }
});

const WebSocket = require("ws");
const server = new WebSocket.Server({ port: 4000 });

server.on("connection", (socket) => {
    console.log("Client connected");

    socket.on("message", (message) => {
        try {
            const textMessage = message.toString();
            const data = JSON.parse(textMessage);
            console.log("Received: ");
            console.log(data);

            server.clients.forEach((client) => {
                if (client.readyState === WebSocket.OPEN) {
                    client.send(textMessage); // Kirim pesan ke semua klien
                }
            });
        } catch (error) {
            console.log(error.message);
        }
    });

    socket.on("close", () => {
        console.log("Client disconnected");
    });
});

console.log("WebSocket server running on ws://localhost:4000");
