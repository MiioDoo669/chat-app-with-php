<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Chat with Client Count</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #e5ddd5;
        }

        #client-count {
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            padding: 10px 0;
            background-color: #f9f9f9;
            border-bottom: 1px solid #ccc;
        }

        #chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 10px;
            overflow-y: auto;
            background-color: #f0f0f0;
        }

        .message {
            display: flex;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .message .icon {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            border-radius: 50%;
            background-color: #0084ff;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            font-size: 16px;
        }

        .message .chat-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 14px;
            line-height: 1.5;
            word-wrap: break-word;
            display: inline-block;
        }

        .message.sender {
            justify-content: flex-end;
        }

        .message.sender .icon {
            background-color: #ffbb33;
            order: 2;
            margin-right: 0;
            margin-left: 10px;
        }

        .message.sender .chat-bubble {
            background-color: #0084ff;
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message.receiver .chat-bubble {
            background-color: #e4e6eb;
            color: black;
            border-bottom-left-radius: 5px;
        }

        form {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: white;
            border-top: 1px solid #ccc;
        }

        input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 20px;
            margin-right: 10px;
            font-size: 14px;
        }

        button {
            padding: 10px 15px;
            background-color: #0084ff;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background-color: #006cdb;
        }
    </style>
</head>
<body>
    <div id="client-count">Total Clients: 0</div>
    <div id="chat-container"></div>
    <form id="chat-form">
        <input type="text" id="username" placeholder="Your name" required>
        <input type="text" id="message" placeholder="Type a message..." required>
        <button type="submit">Send</button>
    </form>

    <script>
        const ws = new WebSocket("ws://localhost:8080");

        const clientCountDiv = document.getElementById("client-count");
        const chatContainer = document.getElementById("chat-container");
        const chatForm = document.getElementById("chat-form");
        const usernameInput = document.getElementById("username");
        const messageInput = document.getElementById("message");

        const getInitials = (name) => name.split(" ").map(word => word[0].toUpperCase()).join("");

        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);

            // If the message is from the server broadcasting the client count
            if (data.type === "clientCount") {
                clientCountDiv.textContent = `Total Clients: ${data.count}`;
            } else if (data.type === "message") {
                // Avoid sending the message twice on the sender side
                if (data.sender !== usernameInput.value) {
                    const messageDiv = document.createElement("div");
                    messageDiv.className = `message ${data.sender === usernameInput.value ? "sender" : "receiver"}`;

                    const iconDiv = document.createElement("div");
                    iconDiv.className = "icon";
                    iconDiv.textContent = getInitials(data.sender);

                    const bubbleDiv = document.createElement("div");
                    bubbleDiv.className = "chat-bubble";
                    bubbleDiv.textContent = data.message;

                    messageDiv.appendChild(iconDiv);
                    messageDiv.appendChild(bubbleDiv);
                    chatContainer.appendChild(messageDiv);
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            }
        };

        chatForm.addEventListener("submit", (event) => {
            event.preventDefault();
            const message = messageInput.value;
            const username = usernameInput.value;

            const messageDiv = document.createElement("div");
            messageDiv.className = "message sender";

            const iconDiv = document.createElement("div");
            iconDiv.className = "icon";
            iconDiv.textContent = getInitials(username);

            const bubbleDiv = document.createElement("div");
            bubbleDiv.className = "chat-bubble";
            bubbleDiv.textContent = message;

            messageDiv.appendChild(iconDiv);
            messageDiv.appendChild(bubbleDiv);
            chatContainer.appendChild(messageDiv);

            // Send message via WebSocket
            ws.send(JSON.stringify({ type: "message", sender: username, message }));

            messageInput.value = "";
            chatContainer.scrollTop = chatContainer.scrollHeight;
        });
    </script>
</body>
</html>
