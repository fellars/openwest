<script id="tip-form-editor" type="template">
    {{set "style" "simple"}}
    {{#ifEq style "form"}}
    {{> tip-form-label}}
    {{> tip-form-control-start}}
    {{/ifEq}}
    {{#if buttons}}
    <div class="btn-toolbar">
    {{#each buttons}}
        <div class="btn-group ">
        {{#each this}}
            <button class="btn" data-name="{{name}}" data-title="{{title}}"><i class="icon-{{icon}}"></i></button>
        {{/each}}
        </div>
    {{/each}}
    </div>
    {{/if}}

            <div id="{{cid}}" style="width: 400px; height: 500px; position:relative;">{{{content}}}</div>
    {{#ifEq style "form"}}
        {{> tip-form-errors}}
        {{> tip-form-help}}
        {{> tip-form-control-end}}
    {{/ifEq}}
</script>
