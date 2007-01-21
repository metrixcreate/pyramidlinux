<?
if($count_time)
{
	$time_stop = getmicrotime();
	$time_diff = $time_stop - $time_start;
	echo "<p class=\"time\">Page rendered in ".round($time_diff,3)." sec</p>";
}
?>
</td></tr></table>
</body>
</html>

