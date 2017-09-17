all: equinoxsundistance.png equinoxcircumference.png

clean:
	rm equinoxsundistance.png equinoxcircumference.png

equinoxsundistance.png: equinoxsundistance.gnuplot
	gnuplot equinoxsundistance.gnuplot

equinoxcircumference.png: equinoxcircumference.gnuplot
	gnuplot equinoxcircumference.gnuplot

