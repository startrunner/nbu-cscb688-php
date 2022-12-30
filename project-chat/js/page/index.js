(() => {
    console.log('Hello, World!');

    const btn_newChat = document.getElementById('btn-new-chat');
    btn_newChat.addEventListener('click', async() => {
        const userInfo = await runUserDialog();
        if (!userInfo) return;

        await openChat(userInfo);
    });

    const form_sender = document.querySelector('#chat-sender');
    form_sender.addEventListener("submit", async e => {
        e.preventDefault();
        const textInput = form_sender.querySelector('#input-message-text');

        const to = getOtherUserLogin();
        if (!to) return;

        const messageText = textInput.value;
        if (!messageText) return;

        const message = { text: messageText, to };

        await sendMessage(message);

        textInput.value = null;
    });

    (async() => {
        //await delayPromise(1000);
        for (;;) {
            const tasks = [
                fetchAndRenderMessages(),
                fetchAndRenderRecents(),
            ];
            for (const x of tasks) await x;
            await delayPromise(500);
        }
    })();

    async function runUserDialog() {
        let promptMessage = "Please enter your friend's username";
        for (;;) {
            const username = prompt(promptMessage, '');
            if (username === null) return null; //Canceled
            const userInfo = await getUserInfo(username);
            if (!userInfo) {
                promptMessage = `User ${username} does not exist. Try again.`;
                continue;
            }

            return userInfo;
        }
    }

    async function openChat(userInfo) {
        const { login } = userInfo;
        if (getOtherUserLogin() == login) return;
        const url = new URL(window.location.href);
        url.searchParams.set('pm', login);
        window.location.href = url;
    }

    function getOtherUserLogin() {
        const url = new URL(window.location.href);
        const login = url.searchParams.get('pm');
        return login || null;
    }

    async function getUserInfo(username) {
        const query = new URLSearchParams();
        query.set('u', username);
        const url = '/api/users/get-user-info.php?' + query;
        const response = await fetch(url);
        if (response.status != 200) return null;
        const responseBody = await response.json();
        return responseBody;
    }

    async function sendMessage(message) {
        console.log("Sending", message);

        const url = "/api/messages/send-message.php";
        const response = await fetch(url, {
            method: 'POST',
            body: JSON.stringify(message),
        })
    }

    async function fetchAndRenderRecents() {
        const recents = await fetchRecents();
        if (!(recents && recents.length)) return;
        renderRecents(recents);
    }

    function renderRecents(recentItems) {
        const divRecents = document.querySelector('#recents');

        for (const recent of recentItems) {
            const { otherUserId, otherUserFullName, otherUserBadgeText, messageTime, messageText, otherUserLogin } = recent;

            const elementId = `recent-chat-${otherUserId}`;

            const recentItemElement = (() => {
                const existingElement = divRecents.querySelector(`#${elementId}`);
                if (existingElement) return existingElement;

                const newElement = document.createElement('a');
                newElement.id = elementId;
                newElement.classList.add('recent-item');
                const hrefQuery = new URLSearchParams();
                hrefQuery.set('pm', otherUserLogin);
                newElement.href = `/?${hrefQuery}`;

                const badgeElement = document.createElement('span');
                badgeElement.innerText = otherUserBadgeText;
                badgeElement.classList.add('w3-badge');
                badgeElement.classList.add('recent-item-badge');
                newElement.appendChild(badgeElement);

                const fullNameElement = document.createElement('span');
                fullNameElement.innerText = otherUserFullName;
                fullNameElement.classList.add('recent-item-fullname');
                newElement.appendChild(fullNameElement);

                const messageTextElement = document.createElement('span');
                messageTextElement.classList.add('recent-item-messagetext');
                newElement.appendChild(messageTextElement);

                //newElement.addEventListener("click", () => {
                //    openChat({ login: otherUserLogin });
                //});

                divRecents.appendChild(newElement);
                return newElement;
            })();

            const messageTextElement = recentItemElement.querySelector('.recent-item-messagetext');
            if (messageTextElement.innerText !== messageText)
                messageTextElement.innerText = messageText;

            const isActive = getOtherUserLogin() === otherUserLogin;
            if (!isActive) recentItemElement.classList.remove('active');
            else recentItemElement.classList.add('active');

            // debugger
        }
    }

    async function fetchRecents() {
        {
            const metaTag = document.querySelector(`meta[name=data-recent-messages]`);
            if (metaTag) {
                const json = metaTag.content;
                const recents = JSON.parse(json);
                metaTag.parentElement.removeChild(metaTag);
                return recents;
            }
        }



        const url = '/api/messages/fetch-recents.php?';

        const response = await fetch(url);
        if (response.status !== 200) return [];
        const messages = await response.json();
        return messages;
    }

    async function fetchAndRenderMessages() {
        const otherUserLogin = getOtherUserLogin();
        const messages = await fetchMessages(otherUserLogin);
        if (!(messages && messages.length)) return;
        renderMessages(messages);
    }

    async function fetchMessages(otherUserLogin) {
        const query = new URLSearchParams();
        query.set('pm', otherUserLogin);
        const url = '/api/messages/fetch-messages.php?' + query;

        const response = await fetch(url);
        if (response.status !== 200) return [];
        const messages = await response.json();
        return messages;
    }

    function renderMessages(messages) {
        const messagesDiv = document.querySelector("#chat-messages");
        if (!messagesDiv) return;

        for (const message of messages) {
            const { messageId, isSentByCurrentUser, time, messageText } = message;
            //console.log("Message: ", { messageId, isSentByCurrentUser, time, messageText });
            const elementId = `message-${messageId}`;

            let messageElementAdded = false;

            const messageElement = (() => {
                const existingElement = messagesDiv.querySelector(`#${elementId}`);
                if (existingElement) {
                    return existingElement;
                }

                const rowElement = document.createElement('div');
                const rowClasses = rowElement.classList;
                rowClasses.add("message-row");

                const newElement = document.createElement('div');
                newElement.id = elementId;
                newElement.innerText = messageText;
                const messageClasses = newElement.classList;
                messageClasses.add('message');

                if (isSentByCurrentUser) {
                    messageClasses.add('message-by-me');
                    rowClasses.add('message-row-by-me');
                } else {
                    messageClasses.add('message-not-by-me');
                    rowClasses.add('message-row-not-by-me');

                }

                messagesDiv.appendChild(rowElement);
                rowElement.appendChild(newElement);

                messageElementAdded = true;
                return newElement;

            })();

            const doScrollToBottom = messageElementAdded && message == messages[messages.length - 1];
            if (doScrollToBottom) {
                const scrollHeight = messagesDiv.scrollHeight;
                if (!isNaN(scrollHeight) && scrollHeight > 0)
                    messagesDiv.scrollTo(0, messagesDiv.scrollHeight);
            }

        }
    }


    async function delayPromise(timeoutMS) {
        return new Promise(ok => {
            setTimeout(() => {
                ok();
            }, timeoutMS);
        })
    }

})();