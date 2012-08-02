/*
<div id="system-message-container">
<dl id="system-message">
<dt class="message">Nachricht</dt>
<dd class="message message">
	<ul>
		<li>test</li>
	</ul>
</dd>
</dl>
</div>
 */

Joomla.systemMessage = function(msg, type) {
    var el = Elements.from('<dl id="system-message"><dt class="message">Nachricht</dt><dd class="message"><ul><li>' + msg + '</li></ul></dd></dl>');
    el.inject($("system-message-container"));
}

