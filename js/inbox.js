document.addEventListener('DOMContentLoaded', function() {
    const conversationsContainer = document.getElementById('conversations-container');
    const messageViewContent = document.getElementById('message-view-content');
    let currentConversationId = null;
    let currentOtherUserId = null;

    async function fetchConversations() {
        try {
            const response = await fetch('api/get_conversations.php');
            const conversations = await response.json();
            renderConversations(conversations);
        } catch (error) {
            console.error('Error fetching conversations:', error);
        }
    }

    function renderConversations(conversations) {
        conversationsContainer.innerHTML = '';
        if (conversations.length === 0) {
            conversationsContainer.innerHTML = '<p class="p-3 text-muted">No conversations yet.</p>';
            return;
        }
        conversations.forEach(convo => {
            const convoItem = document.createElement('div');
            convoItem.className = 'conversation-item';
            convoItem.dataset.conversationId = convo.conversation_id;
            convoItem.dataset.otherUserId = convo.other_user_id;
            convoItem.innerHTML = `
                <div class="conversation-details">
                    <h6>${convo.other_user_name}</h6>
                    <p>${convo.last_message || 'No messages yet'}</p>
                </div>
            `;
            convoItem.addEventListener('click', () => {
                const currentlyActive = document.querySelector('.conversation-item.active');
                if (currentlyActive) {
                    currentlyActive.classList.remove('active');
                }
                convoItem.classList.add('active');
                currentConversationId = convo.conversation_id;
                currentOtherUserId = convo.other_user_id;
                fetchMessages(convo.conversation_id, convo.other_user_id);
            });
            conversationsContainer.appendChild(convoItem);
        });
    }

    async function fetchMessages(conversationId, otherUserId) {
        try {
            const response = await fetch(`api/get_messages.php?conversation_id=${conversationId}`);
            const messages = await response.json();
            renderMessages(messages, otherUserId);
        } catch (error) {
            console.error('Error fetching messages:', error);
        }
    }

    function renderMessages(messages, otherUserId) {
        messageViewContent.innerHTML = `
            <div id="message-list-wrapper" class="message-list-wrapper"></div>
            <form id="message-form" class="message-form">
                <div class="input-group">
                    <input type="text" id="message-input" class="form-control" placeholder="Type a message..." required>
                    <button type="submit" class="btn btn-primary">Send</button>
                </div>
            </form>
        `;

        const messageListWrapper = document.getElementById('message-list-wrapper');
        messages.reverse().forEach(msg => {
            const messageBubble = document.createElement('div');
            messageBubble.className = `message-bubble ${msg.sender_id == otherUserId ? 'received' : 'sent'}`;
            messageBubble.textContent = msg.body;
            messageListWrapper.appendChild(messageBubble);
        });

        const messageForm = document.getElementById('message-form');
        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const messageInput = document.getElementById('message-input');
            const body = messageInput.value.trim();
            if (body) {
                await sendMessage(currentConversationId, currentOtherUserId, body);
                messageInput.value = '';
                fetchMessages(currentConversationId, currentOtherUserId);
            }
        });
    }

    async function sendMessage(conversationId, receiverId, body) {
        try {
            await fetch('api/send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    conversation_id: conversationId,
                    receiver_id: receiverId,
                    body: body
                })
            });
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    fetchConversations();
});