<!-- include/chatbot.php -->
<div id="chatbot-container" style="display:none; position:fixed; bottom:90px; right:20px; width:320px; max-height:440px; background:#fff; border:1px solid #ccc; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.2); flex-direction: column; overflow:hidden; z-index:1000; font-family: Arial, sans-serif;">
  <div id="chatbot-header" style="background:#007bff; color:#fff; padding:12px 16px; font-weight:bold; font-size:16px; display:flex; justify-content:space-between; align-items:center;">
    📘 BookBot - Need help?
    <button id="chatbot-close" style="background:none; border:none; color:#fff; font-size:22px; cursor:pointer;">&times;</button>
  </div>
  <div id="chatbot-messages" style="padding:10px; height:280px; overflow-y:auto; background:#f9f9f9; display:flex; flex-direction: column;"></div>
  <div id="chatbot-input" style="display:flex; border-top:1px solid #ccc; padding:8px; background:#fff;">
    <input type="text" id="chatbot-user-input" placeholder="Ask something..." style="flex-grow:1; border:1px solid #ccc; border-radius:20px; padding:6px 12px; font-size:14px; outline:none;" />
    <button id="chatbot-voice" title="Voice input" style="margin-left:4px; background:#28a745; color:#fff; border:none; border-radius:50%; padding:6px 10px; font-size:16px; cursor:pointer;">🎤</button>
    <button id="chatbot-send" style="background:#007bff; color:#fff; border:none; margin-left:6px; border-radius:20px; padding:6px 14px; cursor:pointer; font-size:14px;">Send</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const openBtn = document.getElementById('open-chatbot'); // Your button outside chatbot to open it
  const container = document.getElementById('chatbot-container');
  const closeBtn = document.getElementById('chatbot-close');
  const messages = document.getElementById('chatbot-messages');
  const input = document.getElementById('chatbot-user-input');
  const sendBtn = document.getElementById('chatbot-send');
  const voiceBtn = document.getElementById('chatbot-voice');

  // Show chatbot on open button click
  openBtn.addEventListener('click', () => {
    container.style.display = 'flex';
    openBtn.style.display = 'none';
    input.focus();
  });

  // Close chatbot on close button click
  closeBtn.addEventListener('click', () => {
    container.style.display = 'none';
    openBtn.style.display = 'block';
  });

  // Voice input (Web Speech API)
  voiceBtn.addEventListener('click', () => {
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      const recognition = new SpeechRecognition();
      recognition.lang = 'en-US';
      recognition.interimResults = false;
      recognition.maxAlternatives = 1;

      recognition.start();

      recognition.onresult = function (event) {
        const transcript = event.results[0][0].transcript;
        input.value = transcript;
        sendMessage();
      };

      recognition.onerror = function (event) {
        alert("Speech recognition error: " + event.error);
      };
    } else {
      alert("Your browser doesn't support speech recognition.");
    }
  });

  // Send message on button click or Enter key
  sendBtn.addEventListener('click', sendMessage);
  input.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') sendMessage();
  });

  // Send message function
  function sendMessage() {
    const userMsg = input.value.trim();
    if (!userMsg) return;

    appendMessage(userMsg, 'user');
    input.value = '';
    messages.scrollTop = messages.scrollHeight;

    setTimeout(() => {
      const botReply = getBotReply(userMsg);
      appendMessage(botReply, 'bot');
      messages.scrollTop = messages.scrollHeight;
    }, 500);
  }

  // Append message bubble
  function appendMessage(msg, sender) {
    const div = document.createElement('div');
    div.textContent = msg;
    div.style.margin = '8px 0';
    div.style.padding = '10px 14px';
    div.style.borderRadius = '18px';
    div.style.maxWidth = '80%';
    div.style.wordWrap = 'break-word';
    div.style.fontSize = '14px';

    if (sender === 'user') {
      div.style.backgroundColor = '#e1f0ff';
      div.style.alignSelf = 'flex-end';
      div.style.textAlign = 'right';
      div.style.marginLeft = 'auto';
    } else {
      div.style.backgroundColor = '#e6e6e6';
      div.style.alignSelf = 'flex-start';
      div.style.marginRight = 'auto';
    }

    messages.appendChild(div);
  }

  // Basic chatbot logic with categories
  function getBotReply(input) {
    const msg = input.toLowerCase();

    // Greetings
    if (msg.match(/\b(hi|hello|hey|good morning|good afternoon|good evening)\b/)) {
      return "Hi there! 👋 How can I assist you today?";
    }

    // Recommendations
    if (msg.match(/\b(recommend|suggest|books|reading)\b/)) {
      return "📚 You can check the 'Books You Might Like' section on product pages.";
    }

    // Searching & filtering
    if (msg.match(/\b(search|filter|find|look for|browse)\b/)) {
      return "🔎 Use the search bar and filters by language, rating, and price on the homepage.";
    }

    // Wishlist
    if (msg.match(/\b(wishlist|favorite|favorites|save|heart)\b/)) {
      return "💖 Add books to your wishlist by clicking the ♥ icon on any book.";
    }

    // Cart & checkout
    if (msg.match(/\b(cart|buy|purchase|checkout|order|payment)\b/)) {
      return "🛒 Add books to your cart and proceed to checkout to complete your purchase.";
    }

    // Help & FAQ
    if (msg.match(/\b(help|support|faq|question|issue|problem)\b/)) {
      return "ℹ️ I can assist you with searching, filtering, wishlist, cart, and checkout.";
    }

    // Default fallback
    return "🤖 Sorry, I didn’t get that. Try asking about searching, filtering, wishlist, or recommendations.";
  }
});
</script>
