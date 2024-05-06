class TinyZoom{constructor(e){let t=document.querySelectorAll(e);t.forEach(e=>{e.style.cursor="pointer",e.addEventListener("click",()=>{this.makeFullscreen(e)}),e.addEventListener("touchstart",t=>{t.preventDefault(),this.makeFullscreen(e)})})}makeFullscreen(e){let t=document.createElement("div");t.classList.add("fullscreen-image");let i=document.createElement("canvas"),n=i.getContext("2d"),l,a,d,s=window.devicePixelRatio||1;screen.orientation.type.startsWith("portrait"),i.style.maxWidth="85%",i.style.maxHeight="85%",i.width=e.width*s,i.height=e.height*s,n.scale(s,s),n.drawImage(e,0,0,e.width,e.height);var r=Math.max(document.documentElement.clientWidth||0,window.innerWidth||0),h=Math.max(document.documentElement.clientHeight||0,window.innerHeight||0),o=1.1*e.width>r?r/e.width*.9:1,c=1.1*e.height>h?h/e.height*.9:1;function m(t){s*=t,i.style.transform=`scale(${s})`,n.clearRect(0,0,i.width,i.height),n.drawImage(e,0,0,e.width,e.height)}t.appendChild(i),document.body.appendChild(t),t.addEventListener("click",e=>{e.target===t&&t.remove()}),t.addEventListener("wheel",e=>{e.target===t&&e.preventDefault()}),i.style.position="fixed",i.style.left=r/2-e.width/2+"px",i.style.top=h/2-e.height/2+"px",i.style.cursor="move",i.addEventListener("wheel",e=>{e.preventDefault();let t=e.deltaY>0?.9:1.1;m(t)}),i.addEventListener("mousedown",e=>{l=e.pageX-i.offsetLeft,a=e.pageY-i.offsetTop,d=!0}),i.addEventListener("mousemove",e=>{if(d){let t=e.pageX-l,n=e.pageY-a;i.style.left=`${t}px`,i.style.top=`${n}px`}}),i.addEventListener("mouseup",()=>{d=!1}),i.addEventListener("dblclick",e=>{m(e.shiftKey?.5:2)});var p=Math.min(1,o,c);1!=p&&(s=p,i.style.left=r/2-e.width*s/2+"px",i.style.top=h/2-e.height*s/2+"px",m(1))}}window.addEventListener("load",()=>{new TinyZoom(".TinyZoom")});