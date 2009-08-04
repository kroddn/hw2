#!/usr/bin/perl -w

open(INFILE, "/tmp/mailfail.xml") or die "Can't open input.txt: $!";
open(OUTFILE, ">output.txt") or die "Can't open output.txt: $!";

my @text = ;

@text =~ s/E: (.*?)\n\n/eee: $1\n\n/gs;

print OUTFILE @text;
close INFILE;
close OUTFILE;
