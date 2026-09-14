document.addEventListener("DOMContentLoaded", function () {
    const conversationList = document.getElementById("adminConversationList");
    const chatAvatar = document.getElementById("adminChatAvatar");
    const chatUserName = document.getElementById("adminChatUserName");
    const chatUserEmail = document.getElementById("adminChatUserEmail");
    const chatMessages = document.getElementById("adminChatMessages");
    const chatInput = document.getElementById("adminChatInput");
    const chatSend = document.getElementById("adminChatSend");
    const chatBadge = document.getElementById("adminChatBadge");
    if (!conversationList ||
        !chatAvatar ||
        !chatUserName ||
        !chatUserEmail ||
        !chatMessages ||
        !chatInput ||
        !chatSend ||
        !chatBadge) {
        console.error(
            "Admin chat: required HTML elements are missing."
        );
        return;
    }
    let conversations = [];
    let selectedConversationId = 0;
    let conversationsController = null;
    let messagesController = null;
    function getInitial(name) {
        const cleanName =String(name || "").trim();
        if (!cleanName) {
            return "C";
        }
        return cleanName
            .charAt(0)
            .toUpperCase();
    }
    function getCustomerName(conversation) {
        return String(
            conversation?.name ||
            conversation?.username ||
            "Customer"
        ).trim() || "Customer";
    }
    function getProfileImageUrl(profilePicture) {
        const picture=String(profilePicture || "").trim();
        if (!picture) {
            return "";
        }
        return (
            "../images/profile/" +
            encodeURIComponent(picture)
        );
    }
    function setAvatar(
        element,
        name,
        profilePicture
    ) {
        if (!element) {
            return;
        }
        const customerName=String(name || "Customer").trim() ||"Customer";
        const initial = getInitial(customerName);
        element.innerHTML = "";
        element.textContent =initial;
        const imageUrl =getProfileImageUrl(
                profilePicture
            );
        if (!imageUrl) {
            return;
        }
        const image=document.createElement("img");
        image.src=imageUrl;
        image.alt=customerName;
        image.onload=function () {
                element.innerHTML = "";
                element.appendChild(image);
            };
        image.onerror =
            function () {
                console.warn(
                    "Profile image not found:",
                    imageUrl
                );
                element.innerHTML = "";
                element.textContent =initial;
            };
    }
    function createAvatar(conversation) {
        const avatar =document.createElement("div");
        avatar.className ="admin-conversation-avatar";
        setAvatar(
            avatar,
            getCustomerName(conversation),
            conversation.profile_picture
        );
        return avatar;
    }
    async function loadConversations() {
        if (conversationsController) {
            conversationsController.abort();
        }
        conversationsController=new AbortController();
        try {
            const response =await fetch(
                    "chat_messages.php?_=" +
                    Date.now(),
                    {
                        method: "GET",
                        cache: "no-store",
                        signal:
                            conversationsController.signal
                    }
                );
            if (!response.ok) {
                throw new Error(
                    "HTTP error: " +
                    response.status
                );
            }
            const data =await response.json();
            console.log(
                "Admin chat conversations:",
                data
            );
            if (!data.success) {
                throw new Error(
                    data.message ||
                    "Failed to load conversations."
                );
            }
            conversations = Array.isArray(data.conversations)
                    ? data.conversations
                    : [];
            renderConversations();
            updateUnreadBadge();
        } catch (error) {
            if (error.name ==="AbortError") {
                return;
            }
            console.error(
                "Conversation loading error:",
                error
            );
            conversationList.innerHTML =
                `
                <div class="admin-chat-empty">
                    Failed to load conversations.
                </div>
                `;
        }
    }
    function renderConversations() {
        conversationList.innerHTML ="";
        if (conversations.length === 0) {
            conversationList.innerHTML =
                `
                <div class="admin-chat-empty">
                    No open conversations.
                </div>
                `;
            return;
        }
        conversations.forEach(
            function (conversation) {
                const item =document.createElement("button");
                item.type = "button";
                item.className = "admin-conversation-item";
                if (Number(conversation.id) ===Number(selectedConversationId)) {
                    item.classList.add("selected");
                }
                const content =document.createElement("div");
                content.className ="admin-conversation-content";
                const avatar =createAvatar(conversation);
                const text =document.createElement("div");
                text.className ="admin-conversation-text";
                const name =document.createElement("strong");
                name.textContent =getCustomerName(conversation);
                const lastMessage =document.createElement("span");
                lastMessage.textContent = conversation.last_message ||"No messages yet.";
                const bottom = document.createElement("div");
                bottom.className ="admin-conversation-bottom";
                const time =document.createElement("small");
                time.textContent=formatTime(conversation.last_message_time);
                bottom.appendChild(time);
                const unread =Number(conversation.unread_count || 0);
                if (unread > 0) {
                    const unreadBadge =document.createElement(
                            "span"
                        );
                    unreadBadge.className ="admin-conversation-unread";
                    unreadBadge.textContent =
                        unread > 99
                            ? "99+"
                            : String(unread);
                    bottom.appendChild(
                        unreadBadge
                    );
                }
                text.appendChild(
                    name
                );
                text.appendChild(
                    lastMessage
                );
                text.appendChild(
                    bottom
                );
                content.appendChild(
                    avatar
                );
                content.appendChild(
                    text
                );
                item.appendChild(
                    content
                );
                item.addEventListener(
                    "click",
                    function () {
                        const id =Number(conversation.id);
                        if (!id) {
                            return;
                        }
                        selectedConversationId =id;
                        renderConversations();
                        loadSelectedConversation();
                    }
                );
                conversationList.appendChild(
                    item
                );
            }
        );
    }
    async function loadSelectedConversation() {
        if (!selectedConversationId) {
            resetChat();
            return;
        }
        if (messagesController) {
            messagesController.abort();
        }
        messagesController =new AbortController();
        try {
            const response = await fetch(
                    "get_chat_messages.php?conversation_id=" +
                    encodeURIComponent(
                        selectedConversationId
                    ) +
                    "&_=" +
                    Date.now(),
                    {
                        method: "GET",
                        cache: "no-store",
                        signal:
                            messagesController.signal
                    }
                );
            if (!response.ok) {
                throw new Error(
                    "HTTP error: " +
                    response.status
                );
            }
            const data =await response.json();
            console.log(
                "Selected conversation:",
                data
            );

            if (!data.success) {
                throw new Error(
                    data.message ||
                    "Failed to load conversation."
                );
            }
            renderHeader(
                data.conversation || {}
            );
            renderMessages(
                Array.isArray(data.messages)
                    ? data.messages
                    : []
            );
            const status =String(
                    data.conversation?.status ||
                    ""
                ).trim().toLowerCase();
            const isOpen =status === "open";
            chatInput.disabled =!isOpen;
            chatSend.disabled =!isOpen;
            markRead(selectedConversationId);
        } catch (error) {
            if (error.name ==="AbortError") {
                return;
            }
            console.error(
                "Selected conversation error:",
                error
            );
            chatMessages.innerHTML =
                `
                <div class="admin-chat-empty">
                    Failed to load conversation.
                </div>
                `;
        }
    }
    function resetChat() {
        chatUserName.textContent ="Select a conversation";
        chatUserEmail.textContent ="Choose a customer to start chatting.";
        chatAvatar.innerHTML ="👤";
        chatMessages.innerHTML =
            `
            <div class="admin-chat-empty">
                Select a conversation.
            </div>
            `;
        chatInput.value ="";
        chatInput.disabled=true;
        chatSend.disabled=true;
    }
    function renderHeader(
        conversation
    ) {
        conversation =conversation || {};
        const name =getCustomerName(
                conversation
            );
        chatUserName.textContent =name;
        chatUserEmail.textContent =String(
                conversation.email || ""
            );
        setAvatar(
            chatAvatar,
            name,
            conversation.profile_picture
        );
    }
    function renderMessages(
        messages
    ) {
        chatMessages.innerHTML ="";
        if (!Array.isArray(messages) || messages.length === 0) {
            chatMessages.innerHTML =
                `
                <div class="admin-chat-empty">
                    No messages yet.
                </div>
                `;
            return;
        }
        messages.forEach(
            function (message) {
                const wrapper =document.createElement(
                        "div"
                    );
                const senderType =String(
                        message.sender_type || ""
                    ).toLowerCase();
                if (senderType === "admin") {
                    wrapper.className="admin-chat-message admin-message";
                } else {
                    wrapper.className="admin-chat-message user-message";
                }
                const bubble =document.createElement(
                        "div"
                    );
                bubble.className ="admin-chat-bubble";
                bubble.textContent =message.message || "";
                const time =document.createElement("small");
                time.textContent =formatTime(
                        message.created_at
                    );
                wrapper.appendChild(
                    bubble
                );
                wrapper.appendChild(
                    time
                );
                chatMessages.appendChild(
                    wrapper
                );
            }
        );
        chatMessages.scrollTop =chatMessages.scrollHeight;
    }
    async function sendReply() {
        if (!selectedConversationId) {
            return;
        }
        const message =chatInput.value.trim();
        if (!message) {
            return;
        }
        chatSend.disabled=true;
        try {
            const formData=new FormData();
            formData.append(
                "conversation_id",
                String(
                    selectedConversationId
                )
            );
            formData.append(
                "message",
                message
            );
            const response =await fetch(
                    "send_chat_reply.php",
                    {
                        method: "POST",
                        body: formData
                    }
                );
            if (!response.ok) {
                throw new Error(
                    "HTTP error: " +
                    response.status
                );
            }
            const data =await response.json();
            if (!data.success) {
                throw new Error(
                    data.message ||
                    "Failed to send message."
                );
            }
            chatInput.value ="";
            await loadSelectedConversation();
            await loadConversations();
        } catch (error) {
            console.error(
                "Send message error:",
                error
            );
            alert(
                error.message ||
                "Failed to send message."
            );
        } finally {
            if (selectedConversationId) {
                chatSend.disabled =false;
            }
        }
    }
    async function markRead(conversationId) {
        if (!conversationId) {
            return;
        }
        try {
            const formData =new FormData();
            formData.append(
                "conversation_id",
                String(conversationId)
            );
            const response =await fetch(
                    "mark_chat_read.php",
                    {
                        method: "POST",
                        body: formData
                    }
                );
            if (!response.ok) {
                throw new Error(
                    "HTTP error: " +
                    response.status
                );
            }
            await loadConversations();
        } catch (error) {
            console.error(
                "Mark read error:",
                error
            );
        }
    }
    function updateUnreadBadge() {
        let totalUnread = 0;
        conversations.forEach(
            function (conversation) {
                totalUnread +=
                    Number(
                        conversation.unread_count ||
                        0
                    );
            }
        );
        if (totalUnread > 0) {
            chatBadge.textContent =
                totalUnread > 99
                    ? "99+"
                    : String(totalUnread);
            chatBadge.style.display ="inline-flex";
        } else {
            chatBadge.textContent ="0";
            chatBadge.style.display ="none";
        }
    }
    function formatTime(value) {
        if (!value) {
            return "";
        }
        const rawValue = String(value).trim();
        const date =
            new Date(
                rawValue.replace(
                    " ",
                    "T"
                )
            );
        if (Number.isNaN(date.getTime())) {
            return rawValue;
        }
        return date.toLocaleString(
            [],
            {
                month: "short",
                day: "numeric",
                hour: "numeric",
                minute: "2-digit"
            }
        );
    }
    chatSend.addEventListener(
        "click",
        sendReply
    );
    chatInput.addEventListener(
        "keydown",
        function (event) {
            if (event.key === "Enter" && !event.shiftKey) {
                event.preventDefault();
                sendReply();
            }
        }
    );
    resetChat();
    loadConversations();
    setInterval(
        function () {
            loadConversations();
        },
        5000
    );
    setInterval(
        function () {
            if (selectedConversationId) {
                loadSelectedConversation();
            }
        },
        3000
    );
});