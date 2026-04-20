<?php 
    // Only close the container if we are NOT on the homepage
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page !== 'index.php'): 
?>

</div> 
<?php endif; ?>
<footer class="bg-dark text-white text-center py-3 mt-5">
    <p>&copy; 2026 Vehicle Rental System. All Rights Reserved.</p>
</footer>

<style>
    /* Chatbot CSS */
    #chat-widget { position: fixed; bottom: 20px; right: 20px; z-index: 1000; }
    #chat-btn { width: 60px; height: 60px; border-radius: 50%; background-color: #0d6efd; color: white; border: none; box-shadow: 0 4px 8px rgba(0,0,0,0.2); font-size: 24px; cursor: pointer; transition: 0.3s; }
    #chat-btn:hover { transform: scale(1.1); }
    #chat-box { width: 350px; height: 450px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); display: none; flex-direction: column; overflow: hidden; position: absolute; bottom: 75px; right: 0; }
    #chat-header { background: #0d6efd; color: white; padding: 15px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
    #chat-messages { flex: 1; padding: 15px; overflow-y: auto; background: #f8f9fa; display: flex; flex-direction: column; gap: 10px; }
    #chat-input-area { display: flex; border-top: 1px solid #ddd; padding: 10px; background: white; }
    #chat-input { flex: 1; border: 1px solid #ccc; border-radius: 20px; padding: 8px 15px; outline: none; }
    #send-btn { background: #0d6efd; color: white; border: none; border-radius: 50%; width: 40px; height: 40px; margin-left: 10px; cursor: pointer; }
    
    /* Message Bubbles */
    .msg-bubble { max-width: 80%; padding: 10px 15px; border-radius: 15px; font-size: 0.9em; line-height: 1.4; }
    .bot-msg { background: #e9ecef; color: #333; align-self: flex-start; border-bottom-left-radius: 2px; }
    .user-msg { background: #0d6efd; color: white; align-self: flex-end; border-bottom-right-radius: 2px; }
</style>

<div id="chat-widget">
    <div id="chat-box">
        <div id="chat-header">
            <span><i class="fa-solid fa-robot"></i> Premium Support</span>
            <button onclick="toggleChat()" style="background:none; border:none; color:white; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <div id="chat-messages">
            <div class="msg-bubble bot-msg">Hi! 👋 I'm your virtual assistant. Ask me anything about our rentals!</div>
        </div>
        <div id="chat-input-area">
            <input type="text" id="chat-input" placeholder="Type a message..." onkeypress="handleKeyPress(event)">
            <button id="send-btn" onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>
    <button id="chat-btn" onclick="toggleChat()">
        <i class="fa-solid fa-comment-dots"></i>
    </button>
</div>

<script>
    const chatBox = document.getElementById('chat-box');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');

    function toggleChat() {
        chatBox.style.display = chatBox.style.display === 'flex' ? 'none' : 'flex';
    }

    function handleKeyPress(e) {
        if (e.key === 'Enter') sendMessage();
    }

    function appendMessage(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `msg-bubble ${sender === 'user' ? 'user-msg' : 'bot-msg'}`;
        // Basic bold formatting for the bot
        msgDiv.innerHTML = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>'); 
        chatMessages.appendChild(msgDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight; // Auto-scroll
    }

    function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;

        // Show user message
        appendMessage(text, 'user');
        chatInput.value = '';

        // Add "typing..." indicator
        const typingId = 'typing-' + Date.now();
        const typingDiv = document.createElement('div');
        typingDiv.id = typingId;
        typingDiv.className = 'msg-bubble bot-msg';
        typingDiv.innerHTML = '<i class="fa-solid fa-ellipsis fa-fade"></i>';
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Send to backend via AJAX
        fetch('chatbot_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById(typingId).remove(); // Remove typing indicator
            appendMessage(data.reply, 'bot'); // Show bot response
        })
        .catch(error => {
            document.getElementById(typingId).remove();
            appendMessage("Sorry, I'm having trouble connecting to the server.", 'bot');
        });
    }
</script>

</body>
</html>