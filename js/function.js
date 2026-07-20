let currentIndex1 = 0;
function plusDivs(step) {
  const container = document.querySelector(".coberturas-slide"); 
  const items = container.querySelectorAll(".cobertura");        
  const itemWidth = items[0].offsetWidth;
  // Detectar si es móvil o escritorio
  const isMobile = window.innerWidth <= 820; 
  const visibleCount = isMobile ? 1 : 3;     // 👈 1 en móvil, 3 en escritorio
  const totalItems = items.length;
  const maxIndex = totalItems - visibleCount;
  currentIndex1 += step;
  if (currentIndex1 > maxIndex) {
    currentIndex1 = 0;
  }
  if (currentIndex1 < 0) {
    currentIndex1 = maxIndex;
  }
  container.scrollTo({
    left: itemWidth * currentIndex1,
    behavior: 'smooth'
  });
}
// Ajustar índice si cambia el tamaño de pantalla
window.addEventListener("resize", () => {
  currentIndex1 = 0;
  document.querySelector(".coberturas-slide").scrollTo({ left: 0 });
});


const track = document.querySelector('.adicionales-slide');
const items = document.querySelectorAll('.adicionales');
const dotsContainer = document.querySelector('.dots');

let currentIndex = 0;
let itemsPerView = 3;
let totalDots = 0;

function updateItemsPerView() {
  itemsPerView = window.innerWidth <= 820 ? 1 : 3;
}

function calculateDots() {
  if (itemsPerView === 1) {
    // 📱 Mobile: 1 dot por item
    totalDots = items.length;
  } else {
    // 🖥 Desktop: posiciones posibles
    totalDots = items.length - itemsPerView + 1;
  }
}

function createDots() {
  dotsContainer.innerHTML = '';

  for (let i = 0; i < totalDots; i++) {
    const dot = document.createElement('span');
    dot.classList.add('dot');
    if (i === currentIndex) dot.classList.add('active');

    dot.addEventListener('click', () => moveTo(i));
    dotsContainer.appendChild(dot);
  }
}

function moveTo(index) {
  const maxIndex = totalDots - 1;

  // 🔁 LOOP
  if (index > maxIndex) {
    currentIndex = 0;
  } else if (index < 0) {
    currentIndex = maxIndex;
  } else {
    currentIndex = index;
  }

  const offset = (currentIndex * 100) / itemsPerView;
  track.style.transform = `translateX(-${offset}%)`;

  document.querySelectorAll('.dot').forEach((dot, i) => {
    dot.classList.toggle('active', i === currentIndex);
  });
}

function next() {
  moveTo(currentIndex + 1);
}

function initSlider() {
  updateItemsPerView();
  calculateDots();
  createDots();
  moveTo(0);
}

window.addEventListener('resize', () => {
  currentIndex = 0;
  initSlider();
});

initSlider();



function down(id) {
	  var x = document.getElementById(id);
	  let y=id+"-i";
	  var i = document.getElementById(y);
	  if (x.className.indexOf("show") == -1) {
	    x.className += " show";
	    i.innerHTML="-";
	  } else { 
	    x.className = x.className.replace(" show", "");
	    i.innerHTML="+";
	  }
}

if (screen.width > 821){
let currentIndex2 = 0;
function plusDivs2(step2) {
  const container2 = document.querySelector(".tiposseguros-slide"); 
  const items2 = container2.querySelectorAll(".tiposseguros");      
  const itemWidth2 = items2[0].offsetWidth;
  // Detectar si es móvil o escritorio
  const isMobile2 = window.innerWidth <= 820; 
  const visibleCount2 = isMobile2 ? 1 : 3;     // 👈 1 en móvil, 3 en escritorio
  const totalItems2 = items2.length;
  const maxIndex2 = totalItems2 - visibleCount2;
  currentIndex2 += step2;
  if (currentIndex2 > maxIndex2) {
    currentIndex2 = 0;
  }
  if (currentIndex2 < 0) {
    currentIndex2 = maxIndex2;
  }
  container2.scrollTo({
    left: itemWidth2 * currentIndex2,
    behavior: 'smooth'
  });
}
// Ajustar índice si cambia el tamaño de pantalla
window.addEventListener("resize", () => {
  currentIndex2 = 0;
  document.querySelector(".tiposseguros-slide").scrollTo({ left: 0 });
});
}

const track2 = document.querySelector('.herramientas-slide');
const items4 = document.querySelectorAll('.herramientas');
const dotsContainer2 = document.querySelector('.dots2');

let currentIndex4 = 0;
let itemsPerView2 = 3;
let totalDots2 = 0;

function updateItemsPerView2() {
  itemsPerView2 = window.innerWidth <= 820 ? 1 : 3;
}

function calculateDots2() {
  if (itemsPerView2 === 1) {
    // 📱 Mobile: 1 dot por item
    totalDots2 = items4.length;
  } else {
    // 🖥 Desktop: posiciones posibles
    totalDots2 = items4.length - itemsPerView2 + 1;
  }
}

function createDots2() {
  dotsContainer2.innerHTML = '';

  for (let i = 0; i < totalDots2; i++) {
    const dot2 = document.createElement('span');
    dot2.classList.add('dot2');
    if (i === currentIndex) dot2.classList.add('active');

    dot2.addEventListener('click', () => moveTo2(i));
    dotsContainer2.appendChild(dot2);
  }
}

function moveTo2(index2) {
  const maxIndex2 = totalDots2 - 1;

  // 🔁 LOOP
  if (index2 > maxIndex2) {
    currentIndex2 = 0;
  } else if (index2 < 0) {
    currentIndex2 = maxIndex2;
  } else {
    currentIndex2 = index2;
  }

  const offset2 = (currentIndex2 * 100) / itemsPerView2;
  track2.style.transform = `translateX(-${offset2}%)`;

  document.querySelectorAll('.dot2').forEach((dot2, i) => {
    dot2.classList.toggle('active', i === currentIndex2);
  });
}

function next2() {
  moveTo2(currentIndex2 + 1);
}

function initSlider2() {
  updateItemsPerView2();
  calculateDots2();
  createDots2();
  moveTo2(0);
}

window.addEventListener('resize', () => {
  currentIndex4 = 0;
  initSlider2();
});

initSlider2();

if (screen.width < 820){ 
    var slideIndex8 = 1;
	showDivs8(slideIndex8);

	function plusDivs8(n) {
	  showDivs8(slideIndex8 += n);
	}

	function currentDiv8(n) {
	  showDivs8(slideIndex8 = n);
	}

	function showDivs8(n) {
	  var i;
	  var x = document.getElementsByClassName("card");
	  var dots8 = document.getElementsByClassName("demo");
	  if (n > x.length) {slideIndex8 = 1}
	  if (n < 1) {slideIndex8 = x.length}
	  for (i = 0; i < x.length; i++) {
	    x[i].style.display = "none";  
	  }
	  for (i = 0; i < dots8.length; i++) {
	    dots8[i].className = dots8[i].className.replace(" white", "");
	  }
	  x[slideIndex8-1].style.display = "block";  
	  dots8[slideIndex8-1].className += " white";
	}	

    var slideIndex9 = 1;
	showDivs9(slideIndex9);

	function plusDivs9(n) {
	  showDivs9(slideIndex9 += n);
	}

	function currentDiv9(n) {
	  showDivs9(slideIndex9 = n);
	}

	function showDivs9(n) {
	  var i;
	  var x = document.getElementsByClassName("cuenta");
	  var dots9 = document.getElementsByClassName("demo2");
	  if (n > x.length) {slideIndex9 = 1}
	  if (n < 1) {slideIndex9 = x.length}
	  for (i = 0; i < x.length; i++) {
	    x[i].style.display = "none";  
	  }
	  for (i = 0; i < dots9.length; i++) {
	    dots9[i].className = dots9[i].className.replace(" white", "");
	  }
	  x[slideIndex9-1].style.display = "block";  
	  dots9[slideIndex9-1].className += " white";
	}	


    var slideIndex7 = 1;
	showDivs7(slideIndex7);

	function plusDivs7(n) {
	  showDivs7(slideIndex7 += n);
	}

	function showDivs7(n) {
	  var i;
	  var x = document.getElementsByClassName("box");
	  if (n > x.length) {slideIndex7 = 1}
	  if (n < 1) {slideIndex7 = x.length}
	  for (i = 0; i < x.length; i++) {
	    x[i].style.display = "none";  
	  }
	  x[slideIndex7-1].style.display = "block";
	}	


}
