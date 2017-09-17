set grid

set xrange [-90:90]
set yrange [0.0:60000]
set xr [-90:90]
set yr [0.0:60000]

set title 'Circumference to Latitude'
set xlabel 'Circumference'
set ylabel 'FE Sun Distance'

set output 'equinoxcircumference.png'
set terminal pngcairo  background "#ffffff" enhanced font "arial,8" fontscale 1.0 size 500, 300
plot 'equinoxcircumference.dat'
