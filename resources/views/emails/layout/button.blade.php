<?php
    if (!isset($style)) $style = 'btn-primary';
?>
<table border="0" cellpadding="0" cellspacing="0" class="btn {{$style}}">
  <tbody>
    <tr>
      <td align="left">
        <table border="0" cellpadding="0" cellspacing="0">
          <tbody>
            <tr>
              <td> <a href="{{$url}}" target="_blank">{!! $label !!}</a> </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </tbody>
</table>