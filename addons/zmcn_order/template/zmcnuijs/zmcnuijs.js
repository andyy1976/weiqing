//--
var mouseStartPoint = {"left":0,"top":  0};
var mouseEndPoint = {"left":0,"top":  0};
var mouseDragDown = false;
var oldP = {"left":0,"top":  0};
var moveTartet ;
$(document).ready(function(){
	$(document).on("mousedown",".modal-header",function(e){
		if($(e.target).hasClass("close"))
			return;
		mouseDragDown = true;
		moveTartet = $(this).parent();
		mouseStartPoint = {"left":e.clientX,"top":  e.clientY};
		oldP = moveTartet.offset();
	});
	$(document).on("mouseup",function(e){
		mouseDragDown = false;
		moveTartet = undefined;
		mouseStartPoint = {"left":0,"top":  0};
		oldP = {"left":0,"top":  0};
	});
	$(document).on("mousemove",function(e){
		if(!mouseDragDown || moveTartet == undefined)return;
		var mousX = e.clientX;
		var mousY = e.clientY;
		if(mousX < 0)mousX = 0;
		if(mousY < 0)mousY = 25;
		mouseEndPoint = {"left":mousX,"top": mousY};
		var width = moveTartet.width();
		var height = moveTartet.height();
		mouseEndPoint.left = mouseEndPoint.left - (mouseStartPoint.left - oldP.left);
		mouseEndPoint.top = mouseEndPoint.top - (mouseStartPoint.top - oldP.top);
		moveTartet.offset(mouseEndPoint);
	});
});
//---
require(['bootstrap'],function($){
	$('[data-toggle="tooltip"]').tooltip();
});