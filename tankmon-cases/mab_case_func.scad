echo(version=version());

transblue = [0.5,0.5,0.9,0.8];

mabWidth = 20;
mabThick = 2.5;

// Create case for "Make a bracket" from bunnings warehouse
//mab_rail_case(56, 20, 8.5, 2);
// Create plug for above case
//mab_rail_case_plug(56,20,2);

module mab_rail_case_plug(intwidth, intlength, thickness)
// Subsection plug for case
color("green") 
    translate([0,0,-1])
    linear_extrude(height=thickness)
        square([intwidth,intlength], center= true);


module mab_rail_case(intwidth, intlength, intheight, thickness) {
// Case primary
    
outerlength = intlength + (thickness*2);
outerwidth = intwidth + (thickness*2);
outerheight = intheight + (thickness*2);

union() {

difference() {
union() {
translate([0,(intlength+thickness)/2,-(thickness+1+mabThick)])
linear_extrude(height=thickness + mabThick)
square(size=[(mabWidth + (thickness*2)),thickness], center=true);

translate([0,-(intlength+thickness)/2,-(thickness+1+mabThick)])
linear_extrude(height=thickness + mabThick)
square(size=[(mabWidth + (thickness*2)),thickness], center=true);
}


// For a square sectional mount
translate([0,0,-mabThick-1])
linear_extrude(height=mabThick)
square(size=[mabWidth,intlength*2], center=true);
}


// Main section
difference() {
color(transblue)
    translate([0,0,-1])
        linear_extrude(height=intheight+thickness)
            square([outerwidth, outerlength], center=true);
color("red") 
    translate([0,0,-2])
    linear_extrude(height=intheight)
        square([intwidth,intlength], center= true);
        
color("black")
    translate([0,outerlength/2-thickness/2,-1])
    linear_extrude(height=4)
        square([10,3], center=true);
}
}

}