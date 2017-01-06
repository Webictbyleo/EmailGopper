	(function($){
	$.ajRequest = $.fn.ajRequest = function(opt){
	self = this,
	$ = jQuery,
	this.defaults = {
		container:'',
		href:window.location.href.split('#')[0],
		preloader: 'http://'+window.location.hostname+'/img/preloader.gif',
		type: "POST",
		data:'',
		autorun:true,
		responseType:'json',
		beforeSend:function(XHR,SET){
	if(ajfxPreview.find("#loader_overlay").length ==0){
	laoderOverlay = $("<div id='loader_overlay'><div></div></div>").css({
	position:"absolute",
	minHeight:ajfxPreview.height(),
	width:ajfxPreview.width(),
	top:0,
	backgroundColor:"rgba(0, 0, 0, 0.38)"
		}).hide();
		ajfxPreview.append(laoderOverlay);
	}
		if(typeof self.defaults.preloader =='string' && self.defaults.preloader !=false){
	img = $("<img alt='saving..' />").attr("src",self.defaults.preloader);
	ajfxPreview.find("#loader_overlay div:first").css({"position":"absolute","text-align":"center",
	width:ajfxPreview.width(),
	bottom:ajfxPreview.height() /2,
	top:ajfxPreview.height() /2,
	backgroundColor:"rgba(0, 0, 0, 0.38)"
	}).html(img);
		}
	ajfxPreview.find("#loader_overlay").slideDown(300);
	
		},
	};
	this.ajRequest = jQuery.extend(this.defaults ,opt);
		this.ajResponse = {
		data:false,
		status:false,
		selector:self,
		};
		this.ajResponse.get = function(){
		
		}
		
		
		
		this.ajRequest.setup = function(){
			self.defaults.container = self;
			self.data('ajRequest',this);
			self.attr('ajRequest',1);
		}
		this.ajRequest.post = function(){
			
			if(self.ajRequest ===undefined)return this;
			
				if(self.attr('ajRequest') !=1){
					throw 'error/aborted';
				}
			if($(self).find("#ajresponse-container").length ===0){
		$(self).append("<div id='ajresponse-container' />");
		}
			
		ajfxPreview = $(self).find("#ajresponse-container");
		self.ajax = $.ajax({
	url: self.defaults.href,
	timeout:600000,
	type:self.defaults.type,
	data:self.defaults.data,
	dataType:self.defaults.responseType,
	cache:false,
	async:true,
	beforeSend:function(XHR,DATA){
		if(self.defaults.beforeSend !==""){
	self.defaults.beforeSend(XHR,DATA);
	}
	},
	});
	
	self.ajax.done(function(data,statusText,XHR){
		
		self.ajResponse.data = data;
		self.ajResponse.status = statusText;
		self.ajResponse.loader = ajfxPreview.find("#loader_overlay");
	});
	
	
	self.ajax.fail(function(XHR,statusText,error){
		self.ajResponse.data = error;
		self.ajResponse.status = statusText;
		self.ajResponse.loader = ajfxPreview.find("#loader_overlay");
			
			
			
		if(statusText =="timeout"){
			
		}
		
		self.ajResponse.loader.find("div:first").fadeOut(300,function(){
			$(this).html('<div class="alert text-error text-danger"><h2>'+self.ajResponse.data+'</h2></div>').fadeIn();
				});
		})
		
		self.ajax.Response = self.ajResponse;
	return self.ajax;
	}
	this.destroy = function(){
		
		ajfxPreview.find("#loader_overlay").detach();
		self.ajax.abort();
		self.removeAttr('ajRequest');
		self.removeData('ajRequest');
		delete self.ajRequest;
		delete self.ajResponse;
		delete self.ajax;
		delete self.defaults;
		delete self;
		
	}
	
	this.ajRequest.setup();
		if(this.defaults.autorun ==true){
	return this.ajRequest.post();
		}else{
			return this.ajRequest;
		}
	
		}
		
	})(jQuery)
	
	
	