use <shaft_mount_func.scad>

// Main section
difference() {
cyl_rail_case(46,21,10,2);

color("red")
    translate([13,0,0])
    linear_extrude(height=16)
        circle(8,$fn=90);

color("red")
    translate([-13,0,0])
    linear_extrude(height=16)
        circle(8,$fn=90);
    
}
