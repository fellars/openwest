Tip.Views.MarkdownDisplay = Tip.Views.Base.extend({
    template: "tip-raw",
    events: {
//      '': ''
    },
    onInit: function(){
        marked.setOptions({
          gfm: true,
          tables: true,
          breaks: false,
          pedantic: false,
          sanitize: false,
          smartLists: true,
          langPrefix: 'language-',
          highlight: function(code, lang) {
            if (lang === 'js') {
              return highlighter.javascript(code);
            }
            return code;
          }
        });
        var content = this.get('content',"");
        var html = marked(content);
        this.set('html',html);
    },
    update: function(content){
        this.set('content',content);
        var html = marked(content);
        this.set('html',html);
        this.render();
    },
    getRendered: function(){
        return this.get('html');
    },
    editorButtons: function(){
        return [
            [
                {name:"bold",title: "Bold - Ctrl+B",icon:"bold", key:'B' },
                {name:"italic",title: "Italic - Ctrl+I",icon:"italic", key:'I' }
            ],
            [
                {name:"link",title: "Link - Ctrl+L",icon:"link", key:'L' },
                {name:"quote",title: "Blockquote - Ctrl+Q",icon:"quote-left", key:'Q' },
                {name:"code",title: "Code - Ctrl+K",icon:"list-alt", key:'K' },
                {name:"media",title: "Media - Ctrl+M",icon:"picture", key:'M' }
            ],
            [
                {name:"numberList",title: "Numbered List - Ctrl+O",icon:"list-ol", key:'O' },
                {name:"bulletList",title: "Bulleted List - Ctrl+U",icon:"list-ul", key:'U' },
                {name:"header",title: "Heading - Ctrl+H",icon:"text-height", key:'H' },
                {name:"hr",title: "Horizontal Rule - Ctrl+R",icon:"minus", key:'R' }
            ],
            [
                {name:"undo",title: "Undo - Ctrl+Z",icon:"undo", key:'Z' },
                {name:"redo",title: "Redo - Ctrl+Y",icon:"repeat", key:'Y' }
            ]
        ];
    },
    editorButton: function(btnName, editor, editorView){
        var selection = editor.getSelection();
        var session = editor.getSession();
        var noSelection = selection.$isEmpty;
        editor.focus();
        var range = selection.getRange();
        var lineRange = range.clone();
        lineRange.setStart(range.start.row,0);
        lineRange.setEnd(range.end.row,editor.getSession().getLine(range.end.row).length);
        switch(btnName){
            case 'bold':
                if(noSelection){
                    editor.insert('____');
                    selection.moveCursorLeft();
                    selection.moveCursorLeft();
                }else{
                    var text = editor.getSession().getTextRange(range);
                    editor.getSession().replace(range,'__'+text+'__');
                }

                break;
            case 'italic':
                if(noSelection){
                    editor.insert('__');
                    selection.moveCursorLeft();
                }else{
                    var text = editor.getSession().getTextRange(range);
                    editor.getSession().replace(range,'_'+text+'_');
                }
                break;
            case 'link':
                var self = this;
                editorView.linkModal(function(response,modal){
                    self.addLink(btnName,editor,response,modal);
                });
                break;
            case 'media':
                var self = this;
                editorView.mediaModal(function(response,modal){
                    self.addLink(btnName,editor,response,modal);
                });
                break;
            case 'quote':
                session.indentRows(range.start.row,range.end.row,'> ');
                break;
            case 'code':
                session.indentRows(range.start.row,range.end.row,'\t');
                break;
            case 'numberList':
                session.indentRows(range.start.row,range.end.row,' 1. ');
                break;
            case 'bulletList':
                session.indentRows(range.start.row,range.end.row,' - ');
                break;
            case 'header':
                session.indentRows(range.start.row,range.end.row,'#');
                break;
            case 'hr':
                editor.insert('\n----------\n');
                break;
            case 'undo':
                editor.undo();
                break;
            case 'redo':
                editor.redo();
                break;
        }

    },
    addLink: function(mode,editor,response,modal){
        debugger;
        if(modal)modal.closeAll();
        var isMedia = (mode == 'media');
        var cursor = editor.getCursorPosition();
        var title = response.get(isMedia ? 'mediaName':'title');
        var url = response.get(isMedia ? 'mediaUrl':'url');
        var regEx = /\[([0-9]*)\]: /;
        var existing = editor.findAll(regEx,{
            regex: true,
            wrap: true
        });
        var ranges = existing === 0 ? [] : (existing > 1 ? editor.getSelection().getAllRanges() : [editor.getSelection().getRange()]);
        var max = 0;
        _.each(ranges,function(r){
            var match = editor.getSession().getTextRange(r);
            var number = regEx.exec(match);
            if(_.size(number || []) > 1){
                number = number[1];
                max = Math.max(max,number);
            }

        },this);
        editor.getSelection().clearSelection();
        editor.navigateFileEnd();
        editor.insert('\n['+ (max+1) +']: ' + url);
        editor.moveCursorToPosition(cursor);
        var image = isMedia ? '!' : '';
        editor.insert(image+'['+ title +']['+ (max+1) +']');

    }

});



