<form action="http://localhost:9080/pub?id=default"
      subscribe_commet_url="http://localhost:9080/sub/default"
      subscribe_web_socket_url="ws://localhost:9080/ws/default">
    <select name="select">
        <option>select connection</option>
        <option>comet</option>
        <option>webSocket</option>
    </select>
    <input type="text" name="message" autocomplete="off">
    <input type="submit" value="send">
</form>
<script type="text/javascript">
    form = document.getElementsByTagName('form')[0];
    var connections = {};
    form.select.onchange = function () {
        closeConnections();
        if (form.select.value == 'comet') {
            SubscribeCommet(document.getElementsByTagName('body')[0], form.getAttribute('subscribe_commet_url'));
        }

        if (form.select.value == 'webSocket') {
            SubscribeWebSocket(document.getElementsByTagName('body')[0], form.getAttribute('subscribe_web_socket_url'));
        }
    };
    PublishForm(form, form.getAttribute('action'));

    // Посылка запросов -- обычными XHR POST
    function PublishForm(form, url) {

        function sendMessage(message) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", url, true);
            // просто отсылаю сообщение "как есть" без кодировки
            // если бы было много данных, то нужно было бы отослать JSON из объекта с ними
            // или закодировать их как-то иначе
            xhr.send(message);
        }

        form.onsubmit = function () {
            var message = form.message.value;
            if (message) {
                form.message.value = '';
                sendMessage(message);
            }
            return false;
        };
    }

    function showMessage(elem, message) {
        var messageElem = document.createElement('div');
        messageElem.appendChild(document.createTextNode(message));
        elem.appendChild(messageElem);
    }

    // Получение сообщений, COMET
    function SubscribeCommet(elem, url) {
        loadedLen = 0;
        connections.commet = connections.commet || new XMLHttpRequest();

        function subscribe() {
            connections.commet.onreadystatechange = function () {
                if (this.readyState != 3) return;

                if (this.status == 200) {
                    if (this.responseText) {
                        // сервер может закрыть соединение без ответа при перезагрузке
                        showMessage(elem, JSON.parse(this.responseText.substr(loadedLen)).text);
                        loadedLen = this.responseText.length;
                    }
                    subscribe();
                }
            };
        }

        subscribe();
        connections.commet.open("GET", url, true);
        connections.commet.send();
    }

    // Получение сообщений, webSocket
    function SubscribeWebSocket(elem, url) {
        connections.socket = connections.socket || new WebSocket(url);

        connections.socket.onopen = function () {
            console.log("Соединение установлено.");
        };

        connections.socket.onclose = function (event) {
            if (event.wasClean) {
                console.log('Соединение закрыто чисто');
            } else {
                console.log('Обрыв соединения'); // например, "убит" процесс сервера
            }
            console.log('Код: ' + event.code + ' причина: ' + event.reason);
        };

        connections.socket.onmessage = function (event) {
            showMessage(elem, JSON.parse(event.data).text);
        };

        connections.socket.onerror = function (error) {
            console.log("Ошибка " + error.message);
        };
    }

    function closeConnections() {
        if (connections.commet) {
            connections.commet.abort();
            connections.commet = null;
        }

        if (connections.socket) {
            connections.socket.close();
            connections.socket = null;
        }
    }
</script>