pids=$(pgrep -f 'php-fpm: pool')
total_rss=0
count=0
for pid in $pids; do
  rss=$(awk '/VmRSS/ {print $2}' /proc/$pid/status 2>/dev/null || echo 0)
  total_rss=$((total_rss + rss))
  count=$((count + 1))
done
echo "workers: $count, total_rss_kb: $total_rss"
# средняя в МБ:
awk "BEGIN{ if($count>0) print ($total_rss/1024)/$count; else print 0 }"
