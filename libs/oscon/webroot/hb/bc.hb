<script id="bc-match" type="template">
    <div class="row=fluid">
        <div class="span6">{{companyName}}</div>
        <div class="span6"><a class="btn" href="#refresh" data-server="like:company/{{#if companyIsLiked}}0{{else}}1{{/if}}/{{companyId}}/{{matchId}}">{{#if companyIsLiked}}Unlike{{else}}Like{{/if}} Company</a></div>
    </div>
    <div class="row=fluid">
        <div class="span12">{{companyDesc}}</div>
    </div>
    <div class="row=fluid">
        <div class="span6">Opportunity: {{oppName}}</div>
        <div class="span6"><a class="btn" href="#refresh" data-server="like:opportunity/{{#if oppIsLiked}}0{{else}}1{{/if}}/{{oppId}}/{{matchId}}">{{#if oppIsLiked}}Unlike{{else}}Like{{/if}} Opportunity</a></div>
    </div>
    <div class="row=fluid">
        <div class="span12">{{oppDesc}}</div>
    </div>

</script>
<script id="bc-thread" type="template">
    <div style="overflow-y: hidden;" >

        <ul class="messages">
            {{#each thread}}
            <li class="{{who}} clearfix">
                <a class="avatar" href="#">
                    {{#if icon}}<img alt="" src="{{icon}}">{{/if}}
                </a>
                <div class="message">
                    <div class="head clearfix">
                        <span class="name"><strong>{{name}}</strong> says:</span>
                        <span class="time">{{time}}</span>
                    </div>
                    <p>
                        {{{msg}}}
                    </p>
                </div>
            </li>
            {{/each}}
        </ul><!-- end messages -->

    </div>

</script>
<script id="bc-thread-message" type="template">
    <div class="sendMsg span12">
    <form action="#" class="form-horizontal">
        <span location="default"/>
        <button class="send btn btn-danger submit" type="submit">Send message</button>
    </form>
    <span _template_="_template_" data-selector-format="name"/>
</div>

</script>
<script type="template" id="bc-thread-fieldSet">
    <span location="opportunity"/>
    <span location="topic"/>
    <span location="message"/>
    <span _template_="_template_" data-selector-format="name"/>
</div>
</script>
