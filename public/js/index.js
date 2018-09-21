window.onload = function() {
	//TODO  修改滚动时间
	var speed = 30	
	slide2.innerHTML = slide1.innerHTML
	function Marquee() {
		if(slide2.offsetTop - slide.scrollTop<= 0)
			slide.scrollTop -= slide1.offsetHeight
		else {
			slide.scrollTop++
		}
	}
	var MyMar = setInterval(Marquee, speed)
	
};

