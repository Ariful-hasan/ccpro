function show_help(sms, smswidth, x, y, position_id)
{
  element      = document.getElementById('note');
  element.innerHTML = sms;
  //drag_element = document.getElementById(drag_id);
  //exit_element = document.getElementById(exit_id);

  element.style.position   = "absolute";
  element.style.visibility = "visible";
  element.style.display    = "block";
  element.style.width = smswidth + "px";

    var position_element = document.getElementById(position_id);

    for (var p = position_element; p; p = p.offsetParent)
      if (p.style.position != 'absolute')
      {
        x += p.offsetLeft;
        y += p.offsetTop ;
      }

   		y += position_element.clientHeight;

    element.style.left = x+'px';
    element.style.top  = y+'px';
	position_element.onmouseout = kill;
}

function kill()
{
	document.getElementById('note').style.visibility='hidden';
	document.getElementById('note').style.display='none';
}