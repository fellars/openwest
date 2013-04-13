Tip.Views.FormFields.AceEditor = Tip.Views.FormFields.Base.extend({
    type: 'AceEditor',
    template: "tip-form-editor",
    _editor: null,
    events: {
      'click button[data-name]': 'buttonClick'
    },
    fieldInit: function(){

    },
    fieldVal: function(rawOnly){
      var editor = this.editor();
      if(editor){
          var raw = editor.getValue();
          if((!this.get('submitRendered') && _.isUndefined(rawOnly)) || rawOnly){
            return raw;
          }
          var renderView = this.getRenderView();
          if(renderView){
              var value = {
                  raw: raw,
                  rendered: renderView.getRendered()
              };
              return value;
          }
          return raw;
      }
      return this.get('value');
    },
    displayValue: function(){
        var renderView = this.getRenderView();
        if(renderView){
            return renderView.getRendered();
        }
        return this.fieldVal(true);
    },
    getRenderView: function(){
        var renderView = this.get('renderView');
        if(renderView)renderView = this.root.viewFind(renderView);
        return renderView;
    },
    beforeRender: function(){
        var renderView = this.getRenderView();
        if(renderView && !this.get('hideButtons')){
            var buttons = renderView.editorButtons();
            this.set('buttons',buttons);
        }
    },
    afterRender: function(){
        var editor = this.editor();
        editor.setTheme("ace/theme/"+this.get('theme','github'));
        editor.getSession().setMode("ace/mode/"+this.get('mode','markdown'));
        editor.on('change',this.editorChange);
        this.editorChange();
        var renderView = this.getRenderView();
        if(renderView && !this.get('hideButtons')){
            var buttons = renderView.editorButtons();
            _.each(buttons,function(group){
                _.each(group,function(button){
                    if(button.key){
                        var self = this;
                        var command = {
                            name: button.name,
                            exec: function(editor){
                                return self.buttonKeyPress(this,editor);
                            },
                            readOnly: button.readOnly ? true : false
                        };
                        if(_.isString(button.key))command.bindKey = {win: 'Ctrl-'+button.key, mac: 'Command-'+button.key};
                        else if (_.isObject(button.key))command.bindKey = button.key;
                        editor.commands.addCommand(command);
                    }
                },this);
            },this);
        }
    },
    buttonKeyPress: function(button, editor){
        this.handleButton(button.name);
    },
    editorChange: function(e){
        var text = this.fieldVal(true);
        var renderView = this.getRenderView();
        if(renderView){
            renderView.update(text);
        }
        return e;
    },
    editor: function(){
        if(this._editor == null){
            var aceEl = this.$('#'+this.cid).get(0);
            this._editor = ace.edit(aceEl);
        }
        return this._editor;
    },
    buttonClick: function(e){
        Tip.stopEvent(e);
        var name = $(e.currentTarget).data('name');
        this.handleButton(name);
    },
    handleButton: function(name){
        var renderView = this.getRenderView();
        if(name && renderView){
            renderView.editorButton(name,this.editor(),this);
        }

    },
    linkModal: function(callback){
        this._modalView({ server: 'editor:link', formCallback: callback});
    },
    mediaModal: function(callback){
        this._modalView({ server: 'editor:media', formCallback: callback});
    }

});



