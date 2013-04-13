(function() {
  var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};
templates['bc-match'] = template(function (Handlebars,depth0,helpers,partials,data) {
  helpers = helpers || Handlebars.helpers;
  var buffer = "", stack1, foundHelper, functionType="function", escapeExpression=this.escapeExpression, self=this;

function program1(depth0,data) {
  
  
  return "0";}

function program3(depth0,data) {
  
  
  return "1";}

function program5(depth0,data) {
  
  
  return "Unlike";}

function program7(depth0,data) {
  
  
  return "Like";}

function program9(depth0,data) {
  
  
  return "0";}

function program11(depth0,data) {
  
  
  return "1";}

function program13(depth0,data) {
  
  
  return "Unlike";}

function program15(depth0,data) {
  
  
  return "Like";}

  buffer += "      <div class=\"row=fluid\">          <div class=\"span6\">";
  foundHelper = helpers.companyName;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.companyName; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "</div>          <div class=\"span6\"><a class=\"btn\" href=\"#refresh\" data-server=\"like:company/";
  stack1 = depth0.companyIsLiked;
  stack1 = helpers['if'].call(depth0, stack1, {hash:{},inverse:self.program(3, program3, data),fn:self.program(1, program1, data)});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "/";
  foundHelper = helpers.companyId;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.companyId; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "/";
  foundHelper = helpers.matchId;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.matchId; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "\">";
  stack1 = depth0.companyIsLiked;
  stack1 = helpers['if'].call(depth0, stack1, {hash:{},inverse:self.program(7, program7, data),fn:self.program(5, program5, data)});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += " Company</a></div>      </div>      <div class=\"row=fluid\">          <div class=\"span12\">";
  foundHelper = helpers.companyDesc;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.companyDesc; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "</div>      </div>      <div class=\"row=fluid\">          <div class=\"span6\">Opportunity: ";
  foundHelper = helpers.oppName;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.oppName; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "</div>          <div class=\"span6\"><a class=\"btn\" href=\"#refresh\" data-server=\"like:opportunity/";
  stack1 = depth0.oppIsLiked;
  stack1 = helpers['if'].call(depth0, stack1, {hash:{},inverse:self.program(11, program11, data),fn:self.program(9, program9, data)});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "/";
  foundHelper = helpers.oppId;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.oppId; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "/";
  foundHelper = helpers.matchId;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.matchId; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "\">";
  stack1 = depth0.oppIsLiked;
  stack1 = helpers['if'].call(depth0, stack1, {hash:{},inverse:self.program(15, program15, data),fn:self.program(13, program13, data)});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += " Opportunity</a></div>      </div>      <div class=\"row=fluid\">          <div class=\"span12\">";
  foundHelper = helpers.oppDesc;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.oppDesc; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "</div>      </div>    ";
  return buffer;});
})();
(function() {
  var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};
templates['bc-thread'] = template(function (Handlebars,depth0,helpers,partials,data) {
  helpers = helpers || Handlebars.helpers;
  var buffer = "", stack1, functionType="function", escapeExpression=this.escapeExpression, self=this;

function program1(depth0,data) {
  
  var buffer = "", stack1, foundHelper;
  buffer += "              <li class=\"";
  foundHelper = helpers.who;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.who; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + " clearfix\">                  <a class=\"avatar\" href=\"#\">                      ";
  stack1 = depth0.icon;
  stack1 = helpers['if'].call(depth0, stack1, {hash:{},inverse:self.noop,fn:self.program(2, program2, data)});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "                  </a>                  <div class=\"message\">                      <div class=\"head clearfix\">                          <span class=\"name\"><strong>";
  foundHelper = helpers.name;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.name; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "</strong> says:</span>                          <span class=\"time\">";
  foundHelper = helpers.time;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.time; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "</span>                      </div>                      <p>                          ";
  foundHelper = helpers.msg;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.msg; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "                      </p>                  </div>              </li>              ";
  return buffer;}
function program2(depth0,data) {
  
  var buffer = "", stack1, foundHelper;
  buffer += "<img alt=\"\" src=\"";
  foundHelper = helpers.icon;
  if (foundHelper) { stack1 = foundHelper.call(depth0, {hash:{}}); }
  else { stack1 = depth0.icon; stack1 = typeof stack1 === functionType ? stack1() : stack1; }
  buffer += escapeExpression(stack1) + "\">";
  return buffer;}

  buffer += "      <div style=\"overflow-y: hidden;\" >            <ul class=\"messages\">              ";
  stack1 = depth0.thread;
  stack1 = helpers.each.call(depth0, stack1, {hash:{},inverse:self.noop,fn:self.program(1, program1, data)});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "          </ul><!-- end messages -->        </div>    ";
  return buffer;});
})();
(function() {
  var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};
templates['bc-thread-message'] = template(function (Handlebars,depth0,helpers,partials,data) {
  helpers = helpers || Handlebars.helpers;
  


  return "      <div class=\"sendMsg span12\">      <form action=\"#\" class=\"form-horizontal\">          <span location=\"default\"/>          <button class=\"send btn btn-danger submit\" type=\"submit\">Send message</button>      </form>      <span _template_=\"_template_\" data-selector-format=\"name\"/>  </div>    ";});
})();
(function() {
	if (!Handlebars.templateData) Handlebars.templateData = {};
Handlebars.templateData['bc-thread-message'] = {"selectorFormat":"name"};
})();
(function() {
  var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};
templates['bc-thread-fieldSet'] = template(function (Handlebars,depth0,helpers,partials,data) {
  helpers = helpers || Handlebars.helpers;
  


  return "      <span location=\"opportunity\"/>      <span location=\"topic\"/>      <span location=\"message\"/>      <span _template_=\"_template_\" data-selector-format=\"name\"/>  </div>  ";});
})();
(function() {
	if (!Handlebars.templateData) Handlebars.templateData = {};
Handlebars.templateData['bc-thread-fieldSet'] = {"selectorFormat":"name"};
})();
