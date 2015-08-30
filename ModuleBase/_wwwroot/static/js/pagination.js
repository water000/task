Yee.getPagination = function(cur, total, length , start)
{
	length = length || 10;
	start = start || 1;
	var ret = {}, range=function(start, over, obj){for(var i=start; i<=over; i++)obj[i]=i;return obj;};
	if(total <= length)
		return range(start, total, ret);
	switch(cur)
	{
		case 1:
			ret = range(start, length, ret);
			ret[length+1] = '...';
			ret[total] = total;
			break;
		case total:
			var num =total-length+1;
			ret[start] = start;
			var front = Math.floor(total/length)-1;//front should be 0
			ret[front*length] = '...';
			ret = range(num, total, ret);
			break;
		default :
			var mod = cur % length, num;
			if(1 == mod) // the "..." for tail, eg:11,21
			{
				ret[cur-1] = '...';
				num = cur+length-1;
				ret = range(cur, num, ret);
				if(total > num)
					ret[num+1] = '...';
			}
			else if(0 == mod)// the "..." for front, eg:20,30
			{
				num = cur-length+1;
				if(num > length)
					ret[num-1] = '...';
				ret = range(num, cur, ret);
				if(total > cur)
					ret[cur+1] = '...';
			}
			else
			{
				num = Math.floor(cur/length)*length;
				var dest = num+length;
				
				if(0 == num)
				{
					ret = range(num+1, dest, ret);
					ret[dest+1] = '...';
				}
				else if(total == dest)
				{
					ret[start] = start;
					ret[num] = '...';
					ret = range(num+1, dest, ret);
				}
				else
				{
					ret[start] = start;
					ret[num] = '...';
					ret = range(num+1, dest, ret);
					ret[dest+1] = '...';
				}
			}
			ret[total] = total;
			break;
	}
	return ret;
}