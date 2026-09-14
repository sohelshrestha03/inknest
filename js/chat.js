document.addEventListener("DOMContentLoaded", function () {
    const chatButton = document.getElementById("inknestChatButton");
    const chatBox = document.getElementById("inknestChatBox");
    const chatClose = document.getElementById("inknestChatClose");
    const chatMessages = document.getElementById("inknestChatMessages");
    const chatInput = document.getElementById("inknestChatInput");
    const chatSend = document.getElementById("inknestChatSend");
    const chatBadge = document.getElementById("inknestChatBadge");
    if (!chatButton || !chatBox) {
        return;
    }
    let conversationId = null;
    let messageTimer = null;
    chatButton.addEventListener("click", function () {
        chatBox.classList.add("active");
        initializeChat();
    });
    if (chatClose) {
        chatClose.addEventListener("click", function () {
            chatBox.classList.remove("active");
        });
    }
    async function initializeChat() {
        try {
            const response = await fetch(
                "chat/create_conversation.php",
                {
                    method: "POST"
                }
            );
            const data = await response.json();
            if (!data.success) {
                showSystemMessage(
                    data.message || "Unable to start chat."
                );
                return;
            }
            conversationId = data.conversation_id;
            await loadMessages();
            markMessagesRead();
            startPolling();
        } catch (error) {
            console.error(error);
            showSystemMessage(
                "Unable to connect to chat."
            );
        }
    }
    async function loadMessages() {
        if (!conversationId) {
            return;
        }
        try {
            const response = await fetch(
                "chat/get_messages.php?conversation_id=" +
                encodeURIComponent(conversationId) +
                "&t=" +
                Date.now()
            );
            const data = await response.json();
            if (!data.success) {
                return;
            }
            renderMessages(data.messages);
        } catch (error) {
            console.error(error);

        }
    }
    function renderMessages(messages) {
        if (!chatMessages) {
            return;
        }
        chatMessages.innerHTML = "";
        if (!messages || messages.length === 0) {
            const empty = document.createElement("div");
            empty.className="inknest-chat-empty";
            empty.textContent="Hello! How can we help you?";
            chatMessages.appendChild(empty);
            return;
        }
        messages.forEach(function (item) {
            const wrapper=document.createElement("div");
            wrapper.className="inknest-chat-message " +
                (
                    item.sender_type === "user"
                        ? "user-message"
                        : "admin-message"
                );
            const bubble = document.createElement("div");
            bubble.className ="inknest-chat-bubble";
            bubble.textContent =item.message;
            const time=document.createElement("div");
            time.className="inknest-chat-time";
            time.textContent=formatTime(item.created_at);
            wrapper.appendChild(bubble);
            wrapper.appendChild(time);
            chatMessages.appendChild(wrapper);
        });

        chatMessages.scrollTop=chatMessages.scrollHeight;
    }
    async function sendMessage() {
        if (!conversationId) {
            await initializeChat();
        }
        const message=chatInput.value.trim();

        if (message === "") {
            return;
        }
        chatSend.disabled = true;
        try {
            const formData =new FormData();
            formData.append(
                "conversation_id",
                conversationId
            );
            formData.append(
                "message",
                message
            );
            const response=await fetch(
                    "chat/send_message.php",
                    {
                        method: "POST",
                        body: formData
                    }
                );

            const data =await response.json();
            if (!data.success) {
                alert(
                    data.message ||
                    "Message could not be sent."
                );
                return;
            }
            chatInput.value = "";
            await loadMessages();
        } catch (error) {
            console.error(error);
            alert("Unable to send message.");
        } finally {
            chatSend.disabled = false;
            chatInput.focus();
        }
    }
    if (chatSend) {
        chatSend.addEventListener(
            "click",
            sendMessage
        );
    }
    if (chatInput) {
        chatInput.addEventListener(
            "keydown",
            function (event) {
                if (event.key === "Enter" && !event.shiftKey) {
                    event.preventDefault();
                    sendMessage();
                }
            }
        );
    }
    function startPolling() {
        if (messageTimer) {
            clearInterval(messageTimer);
        }
        messageTimer =
            setInterval(
                async function () {
                    await loadMessages();
                    if (chatBox.classList.contains(
                            "active")) {
                        markMessagesRead();
                    }
                },
                3000
            );
    }
    async function markMessagesRead() {
        if (!conversationId) {
            return;
        }
        const formData =new FormData();
        formData.append(
            "conversation_id",
            conversationId
        );
        try {
            await fetch(
                "chat/mark_read.php",
                {
                    method: "POST",
                    body: formData
                }
            );
            updateUnreadCount();
        } catch (error) {
            console.error(error);
        }
    }
    async function updateUnreadCount() {
        try {
            const response=await fetch(
                    "chat/unread_count.php?t=" +
                    Date.now()
                );
            const data=await response.json();
            if (!data.success || data.count <= 0) {
                if (chatBadge) {
                    chatBadge.style.display="none";
                }
                return;
            }
            if (chatBadge) {
                chatBadge.textContent =data.count > 99
                        ? "99+"
                        : data.count;
                chatBadge.style.display ="flex";
            }
        } catch (error) {
            console.error(error);
        }
    }
    function showSystemMessage(message) {
        if (!chatMessages) {
            return;
        }
        chatMessages.innerHTML = "";
        const element =document.createElement("div");
        element.className ="inknest-chat-empty";
        element.textContent =message;
        chatMessages.appendChild(element);
    }
    function formatTime(dateString) {
        const date =new Date(dateString.replace(" ", "T"));
        if (isNaN(date.getTime())) {
            return "";
        }
        return date.toLocaleTimeString(
            [],
            {
                hour: "2-digit",
                minute: "2-digit"
            }
        );
    }
    updateUnreadCount();
    setInterval(
        updateUnreadCount,
        5000
    );
});