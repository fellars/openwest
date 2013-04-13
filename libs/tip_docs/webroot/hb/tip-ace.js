(function() {
  var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};
templates['tip-form-editor'] = template(function (Handlebars,depth0,helpers,partials,data) {
  helpers = helpers || Handlebars.helpers; partials = partials || Handlebars.partials;
  var buffer = "", stack1, foundHelper, self=this, functionType="function", escapeExpression=this.escapeExpression, helperMissing=helpers.helperMissing;

function program1(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "      ";
  stack1 = depth0;
  stack1 = self.invokePartial(partials['tip-form-label'], 'tip-form-label', stack1, helpers, partials);;
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "      ";
  stack1 = depth0;
  stack1 = self.invokePartial(partials['tip-form-control-start'], 'tip-form-control-start', stack1, helpers, partials);;
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "      ";
  return buffer;}

function program3(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "      <div class=\"btn-toolbar\">      ";
  stack1 = depth0.buttons;
  stack1 = helpers.each.call(depth0, stack1, {hash:{},inverse:self.noop,fn:self.program(4, program4, data)});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "      </div>      ";
  return buffer;}
function program4(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "          <div class=\"btn-group \">          ";
  stack1 = helpers.each.call(depth0, depth0, {hash:{},inverse:self.noop,fn:self.program(5, program5, data)});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "          </div>      ";
  return buffer;}
function program5(depth0,data) {
  
  var buffer = "", stack1, foundHelper;
  buffer += "              <button class=\"btn\" data-name=\"";
  foundHelper = helpers.name;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.name; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "\" data-title=\"";
  foundHelper = helpers.title;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.title; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "\"><i class=\"icon-";
  foundHelper = helpers.icon;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.icon; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "\"></i></button>          ";
  return buffer;}

function program7(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "          ";
  stack1 = depth0;
  stack1 = self.invokePartial(partials['tip-form-errors'], 'tip-form-errors', stack1, helpers, partials);;
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "          ";
  stack1 = depth0;
  stack1 = self.invokePartial(partials['tip-form-help'], 'tip-form-help', stack1, helpers, partials);;
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "          ";
  stack1 = depth0;
  stack1 = self.invokePartial(partials['tip-form-control-end'], 'tip-form-control-end', stack1, helpers, partials);;
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "      ";
  return buffer;}

  buffer += "      ";
  foundHelper = helpers.set;
  stack1 = foundHelper ? foundHelper.call(depth0, "style", "simple", {hash:{}}) : helperMissing.call(depth0, "set", "style", "simple", {hash:{}});
  buffer += escapeExpression(stack1) + "      ";
  stack1 = depth0.style;
  foundHelper = helpers.ifEq;
  stack1 = foundHelper ? foundHelper.call(depth0, stack1, "form", {hash:{},inverse:self.noop,fn:self.program(1, program1, data)}) : helperMissing.call(depth0, "ifEq", stack1, "form", {hash:{},inverse:self.noop,fn:self.program(1, program1, data)});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "      ";
  stack1 = depth0.buttons;
  stack1 = helpers['if'].call(depth0, stack1, {hash:{},inverse:self.noop,fn:self.program(3, program3, data)});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "                <div id=\"";
  foundHelper = helpers.cid;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.cid; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "\" style=\"width: 400px; height: 500px; position:relative;\">";
  foundHelper = helpers.content;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.content; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "</div>      ";
  stack1 = depth0.style;
  foundHelper = helpers.ifEq;
  stack1 = foundHelper ? foundHelper.call(depth0, stack1, "form", {hash:{},inverse:self.noop,fn:self.program(7, program7, data)}) : helperMissing.call(depth0, "ifEq", stack1, "form", {hash:{},inverse:self.noop,fn:self.program(7, program7, data)});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "  ";
  return buffer;});
})();
