<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat with API</title>
</head>
<style>
  body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }

  .container {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 400px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  h1 {
    text-align: center;
    color: #333;
  }

  .chat-box {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    height: 300px;
    overflow-y: auto;
    padding: 10px;
    margin-bottom: 10px;
  }

  .chat-box .user-message {
    text-align: right;
    margin-bottom: 10px;
  }

  .chat-box .bot-message {
    text-align: left;
    margin-bottom: 10px;
  }

  .chat-form {
    display: flex;
    gap: 10px;
  }

  .chat-form input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
  }

  .chat-form button {
    padding: 10px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
  }

  .chat-form button:hover {
    background: #0056b3;
  }
</style>

<body>
  <div class="container">
    <h1>AI Chatbot</h1>
    <div id="chat-box" class="chat-box">
    </div>
    <form id="chat-form" class="chat-form">
      <input type="text" id="user-input" placeholder="Type your message here..." required>
      <button type="submit">Send</button>
    </form>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const chatForm = document.getElementById('chat-form');
      const chatBox = document.getElementById('chat-box');
      const userInput = document.getElementById('user-input');

      const appendMessage = (message, sender) => {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add(sender === 'user' ? 'user-message' : 'bot-message');
        messageDiv.textContent = message;
        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
      };

      chatForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const message = userInput.value.trim();
        if (!message) return;

        appendMessage(message, 'user');
        userInput.value = '';

        fetch('open_ai.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              message
            })
          })
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.json();
          })
          .then(data => {
            if (data.reply) {
              appendMessage(data.reply, 'bot');
            } else {
              appendMessage(data.error || 'No response from AI.', 'bot');
            }
          })
          .catch(err => {
            console.error('Error:', err);
            appendMessage('Error communicating with server.', 'bot');
          });
      });

    });
  </script>
</body>

</html>