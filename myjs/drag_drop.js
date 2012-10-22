function Iterator(o, cb){
	for(var i=0; i<o.length; i++){
		cb(o.item(i));
	}
}

function add_blank_holder(){
	if(typeof(blank_holder_added)!='undefined' && blank_holder_added===true) return false;
	
	var blank_holder = $('<div></div>').addClass('blank_holder');
	$('li.step').after(blank_holder.clone());
	
Iterator(
	document.getElementsByClassName('blank_holder'),
	function(o){
		o.addEventListener('dragenter', function(e){e.preventDefault();}, false);
		o.addEventListener('dragover', function(e){
			e.preventDefault();
			e.dataTransfer.dropEffect='copy';
		}, false);
		o.addEventListener('drop', function(e){
			e.preventDefault();
			e.stopPropagation();
			var src_step = e.dataTransfer.getData('text/plain');
			var target_step = $(this).prev('li.step').children('span').attr('stepid');
			$.post('index.php?cmd=order', {
				'step_id':src_step,
				'after_id':target_step
			}, function(data){
				//alert(data);
				location.reload();
				return false;
			});
		}, false);
	}
);

	blank_holder_added = true;
}

$(document).ready(function(){


document.ondragover = function(e){e.preventDefault();}
document.ondrop = function(e){e.preventDefault();}
Iterator(
	document.getElementsByClassName('step'),
	function(o){
		o.draggable = true;
		o.addEventListener('dragstart', function(e){
			e.dataTransfer.effectAllowed = ['copy', 'move'];
			e.dataTransfer.setData('text/plain', $(this).children('span').attr('stepid'));
			add_blank_holder();
		}, false);
		o.addEventListener('dragenter', function(e){e.preventDefault();}, false);
		o.addEventListener('dragover', function(e){
			e.preventDefault();
			e.dataTransfer.dropEffect='copy';
		}, false);
		o.addEventListener('drop', function(e){
			e.preventDefault();
			e.stopPropagation();
			var src_step = e.dataTransfer.getData('text/plain');
			var target_step = $(this).children('span').attr('stepid');
                  console.log([src_step, target_step]);
			$.post('index.php?cmd=combine', {
				'comb_id':src_step,
				'to_id':target_step
			}, function(data){
				//alert(data);
				location.reload();
				return false;
			});
		}, false);
	}
);
Iterator(
	document.getElementsByClassName('planame'),
	function(o){
		o.addEventListener('dragenter', function(e){e.preventDefault();}, false);
		o.addEventListener('dragover', function(e){
			e.preventDefault();
			e.dataTransfer.dropEffect='copy';
		}, false);
		o.addEventListener('drop', function(e){
			e.preventDefault();
			e.stopPropagation();
			var src_step = e.dataTransfer.getData('text/plain');
			var target_step = 0;
			$.post('index.php?cmd=combine', {
				'comb_id':src_step,
				'to_id':target_step
			}, function(data){
				location.reload();
				return false;
			});
		}, false);
	}
);

});
