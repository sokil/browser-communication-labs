<a href="/logout" class="btn btn-danger" id="btnLogout">Log out</a>

<div id="messages-container"><div id="messages">

</div></div>
<form method="post" action="/send" id="frmSend" class="form-inline">
    <div class="form-group">
        <div class="input-group">
            <input type="text" name="message" size="60" class="form-control">
            <span class="input-group-btn">
                <input type="submit" value="Send" class="btn btn-default">
            </span>
        </div>
    </div>
</form>
<script type="text/javascript">
    var $message        = $('#frmSend input[name=message]'),
        $messageList    = $('#messages'),
        ETag            = null,
        time            = null;

    // send message
    $('#frmSend').submit(function(e) {
        e.preventDefault();
        $.post('/send', {message: $message.val()}, function(response) {

            $message.val('');
        }, 'json');
    });

    // get messages from queue
    var pool = function()
    {
        $.ajax({
            url         : '/subscribe?channel=1',
            success     : function(response, textStatus, xhr) {

                ETag = xhr.getResponseHeader('Etag');
                time = xhr.getResponseHeader('Last-Modified');

                var message = '<b>' + response.nick + ' [' + response.time + ']</b> ' + response.text;

                $messageList.prepend($('<div/>').html(message));
                pool();

            },
            dataType    : 'json',
            headers     : {
                'If-None-Match'     : ETag,
                'If-Modified-Since' : time
            } 
        });
    };

    pool();
</script>