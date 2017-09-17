set grid
set angles degrees

set xrange [-90:90]
set yrange [0.0:8000]
set xr [-90:90]
set yr [0.0:8000]
unset key

set title 'Sun Distance to Latitude'
set xlabel 'Latitude (degrees)'
set ylabel 'FE Sun Distance (km)'

set output 'equinoxsundistance.png'
set terminal pngcairo  background "#ffffff" enhanced font "arial,8" fontscale 1.0 size 500, 300
plot 'equinoxsundistance.dat', \
     111 * x  * tan(90-x)