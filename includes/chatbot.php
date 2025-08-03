<style>
  #chatbot-container {
    display: none;
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 320px;
    max-height: 440px;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    flex-direction: column;
    overflow: hidden;
    z-index: 1000;
    font-family: Arial, sans-serif;
    /* needed for flex layout */
    display: none;
    flex-direction: column;
  }
  #chatbot-container.open {
    display: flex;
  }
  #chatbot-header {
    background: #007bff;
    color: #fff;
    padding: 12px 16px;
    font-weight: bold;
    font-size: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  #chatbot-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 22px;
    cursor: pointer;
  }
  #chatbot-messages {
    padding: 10px;
    height: 280px;
    overflow-y: auto;
    background: #f9f9f9;
    display: flex;
    flex-direction: column;
  }
  #chatbot-input {
    display: flex;
    border-top: 1px solid #ccc;
    padding: 8px;
    background: #fff;
  }
  #chatbot-user-input {
    flex-grow: 1;
    border: 1px solid #ccc;
    border-radius: 20px;
    padding: 6px 12px;
    font-size: 14px;
    outline: none;
  }
  #chatbot-voice {
    margin-left: 4px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 50%;
    padding: 8px;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    transition: background 0.3s;
  }
  #chatbot-send {
    background: #007bff;
    color: #fff;
    border: none;
    margin-left: 6px;
    border-radius: 20px;
    padding: 6px 14px;
    cursor: pointer;
    font-size: 14px;
  }
  #open-chatbot {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1001;
    background: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 10px 16px;
    cursor: pointer;
    font-size: 16px;
  }
</style>

<div id="chatbot-container" role="region" aria-label="Chatbot">
  <div id="chatbot-header">
    📘 BookBot - Need help?
    <button id="chatbot-close" aria-label="Close chatbot">&times;</button>
  </div>
  <div id="chatbot-messages" aria-live="polite" aria-atomic="false"></div>
  <div id="chatbot-input">
    <input type="text" id="chatbot-user-input" placeholder="Ask something..." aria-label="Type your message" />
    <button id="chatbot-voice" title="Voice input" aria-label="Start voice input">
      <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20" fill="white" viewBox="0 0 24 24">
        <path d="M12 14a3 3 0 0 0 3-3V5a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3zm5-3a5 5 0 0 1-10 0H5a7 7 0 0 0 14 0h-2zm-5 8a7.07 7.07 0 0 0 4.95-2.05A7.07 7.07 0 0 0 19 12h2a9 9 0 0 1-8 8.95V23h-2v-2.05A9 9 0 0 1 3 12h2a7 7 0 0 0 7 7z"/>
      </svg>
    </button>
    <button id="chatbot-send">Send</button>
  </div>
</div>

<button id="open-chatbot">Chat with BookBot</button>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const openBtn = document.getElementById('open-chatbot');
  const container = document.getElementById('chatbot-container');
  const closeBtn = document.getElementById('chatbot-close');
  const messages = document.getElementById('chatbot-messages');
  const input = document.getElementById('chatbot-user-input');
  const sendBtn = document.getElementById('chatbot-send');
  const voiceBtn = document.getElementById('chatbot-voice');

  // Initially hide chatbot and show open button
  container.classList.remove('open');
  openBtn.style.display = 'block';

  openBtn.addEventListener('click', () => {
    container.classList.add('open');
    openBtn.style.display = 'none';
    input.focus();
  });

  closeBtn.addEventListener('click', () => {
    container.classList.remove('open');
    openBtn.style.display = 'block';
  });

  voiceBtn.addEventListener('click', () => {
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      const recognition = new SpeechRecognition();
      recognition.lang = 'en-US';
      recognition.interimResults = false;
      recognition.maxAlternatives = 1;

      recognition.start();

      recognition.onresult = event => {
        const transcript = event.results[0][0].transcript;
        input.value = transcript;
        sendMessage();
      };

      recognition.onerror = event => {
        alert("Speech recognition error: " + event.error);
      };
    } else {
      alert("Your browser doesn't support speech recognition.");
    }
  });

  sendBtn.addEventListener('click', sendMessage);
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') sendMessage();
  });

  function sendMessage() {
    const userMsg = input.value.trim();
    if (!userMsg) return;

    appendMessage(userMsg, 'user');
    input.value = '';
    messages.scrollTop = messages.scrollHeight;

    setTimeout(() => {
      const botReply = getBotReply(userMsg);
      if (typeof botReply === 'object' && botReply.action === 'filterSortSearch') {
        let params = [];
        if (botReply.search) params.push(`search=${encodeURIComponent(botReply.search)}`);
        if (botReply.language) params.push(`language=${encodeURIComponent(botReply.language)}`);
        if (botReply.sort) params.push(`sort=${encodeURIComponent(botReply.sort)}`);

        appendMessage('🔎 Redirecting with your filters...', 'bot');
        window.location.href = 'index.php' + (params.length ? '?' + params.join('&') : '');
      } else {
        appendMessage(botReply, 'bot');
      }
      messages.scrollTop = messages.scrollHeight;
    }, 500);
  }

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

  function getBotReply(input) {
    const msg = input.toLowerCase();

    let searchTerm = null;
    let language = null;
    let sortValue = null;

    const searchMatch = msg.match(/\b(search for|find) ([\w\s]+)/i);
    if (searchMatch) searchTerm = searchMatch[2].trim();

    const langMatch = msg.match(/\b(filter|search) by language (\w+)/i);
    if (langMatch) language = langMatch[2].trim();

    if (msg.includes('sort by price low to high')) sortValue = 'price_asc';
    else if (msg.includes('sort by price high to low')) sortValue = 'price_desc';
    else if (msg.includes('sort by rating high to low')) sortValue = 'rating_desc';
    else if (msg.includes('sort by rating low to high')) sortValue = 'rating_asc';

    if (searchTerm || language || sortValue) {
      return {
        action: 'filterSortSearch',
        search: searchTerm,
        language: language,
        sort: sortValue
      };
    }

    if (msg.match(/\b(hi|hello|hey|good morning|good afternoon|good evening)\b/)) {
      return "Hi there! 👋 How can I assist you today?";
    }

    if (msg.match(/\b(recommend|suggest|books|reading)\b/i)) {
      return `📚 Looking for something to read? I can suggest books based on your interests!

Explore categories like:
- 🔍 Mystery, Thriller, or Romance
- 🧠 Self-help or Psychology
- 💻 Programming, AI, or Data Science
- 🌍 History, Religion, or Philosophy
- ✨ Fantasy, Sci-fi, or Poetry
- 📖 Biographies or Bestsellers

💡 Explore categories section;`;
    }

    if (msg.match(/\b(search|filter|find|look for|browse)\b/)) {
      return "🔎 Use the search bar and filters by language, rating, and price on the homepage.";
    }

    if (msg.match(/\b(wishlist|favorite|favorites|save|heart)\b/)) {
      return "💖 Add books to your wishlist by clicking the ♥ icon on any book.";
    }

    if (msg.match(/\b(cart|buy|purchase|checkout|order|payment)\b/)) {
      return "🛒 Add books to your cart and proceed to checkout to complete your purchase.";
    }

    if (msg.match(/\b(help|support|faq|question|issue|problem)\b/)) {
      return `ℹ️ I can assist you with:
- Searching books, e.g. "search for [title]"
- Filtering, e.g. "filter by language English"
- Sorting, e.g. "sort by price low to high"
You can also combine these, like:
"search for Node basics filter by language English sort by rating high to low" or use them separately
For wishlist, cart, and checkout, please type one of those cart, checkout, wishlist...;`;
    }

    return "🤖 Sorry, I didn’t get that. Try asking about searching, filtering, wishlist, or recommendations.";
  }
});
</script>
