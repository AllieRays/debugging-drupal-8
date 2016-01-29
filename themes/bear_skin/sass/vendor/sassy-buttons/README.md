Compass CSS Sassy Buttons
=========================

Super sassy and super easy CSS3 buttons.

![sassy buttons](http://dl.dropbox.com/u/1274637/sassy-buttons.png)

Visit the [demo site](http://jaredhardy.com/sassy-buttons/) to see the buttons in action

Installation
============

(See Rails 3.1 notes below)

Install gem from the command line:

    (sudo) gem install sassy-buttons

Installing Sassy Buttons:

    # Edit the project configuration file and add:
    require 'sassy-buttons'

    # From the command line:
    compass install sassy-buttons

    #import sassy buttons partial into your sass/scss file
    @import "sassy-buttons"

Installing Sassy Buttons on Rails 3.1
-------------------------------------
    # Edit your project Gemfile and add the following line to  your :assets group
    gem 'sassy-buttons'

    # Edit your application.css(.sass|.scss) file in the assets/stylesheets folder and add
    @import "sassy-buttons"

    # Bundle your gems to get the sassy-buttons plugin to compass
    $ bundle install

    # Install Sassy Buttons Assets
    $ bundle exec compass install sassy-buttons

Using Sassy Buttons
===================

The Sassy Button mixin

    # The simplest form of the mixin using the provided defaults (See Defaults section below)
    @include sassy-button

    # The Sassy Button mixin takes optional arguments that you can use to customize your buttons on the fly
    # This will create a button with a blue matte style gradient, with a 15px border radius, 20px font size
    @include sassy-button("matte", 15px, 20px, rgba(11, 153, 194, 1))

    # The complete Sassy Button mixin syntax
        @include sassy-button(gradient-style, border-radius, font-size, first-color, second-color, text-color, text-style, auto-states)



Sassy Buttons Gradient Styles
----------------------------
Sassy buttons offer five gradient styles for your buttons. These gradients are generated for you based on the colors you provide. If you don't provide a second color, the mixin will create one for you.

* Simple - A straight gradient from first color to the second color.
* Matte - A gradient that adds depth but retains a matte finish.
* Shiny - A gradient that has a larger highlight area that gives a shinier look.
* Glass - A gradient that provides a glass finish
* Flat - No gradient, button will be given first color provided.

Sassy Buttons Text Styles
-------------------------
Sassy buttons has three text styles

* Inset - Text shadow that makes text look inset on the button
* Raised - Text shadow that makes text look raised on the button
* False - no text style

Sassy Buttons Defaults
----------------------

The Sassy Buttons extension provides a set of default sass variables that are used in the various mixins to create the buttons. These defaults can be overridden to customize your buttons and has the added benefit of having to provide less arguments when calling the sassy button mixin.

    // Base color of button.
    $sb-base-color: rgba(11, 153, 194, 1) !default

    // Optional secondary color for gradient.
    $sb-second-color: false !default

    // Border radius of button.
    $sb-border-radius: 5px !default

    // Padding that gives button structure.
    $sb-padding: 0.3em 1.5em !default

    // Font size.
    $sb-font-size: 16px !default

    // Button font color.
    $sb-text-color: white !default

    // Style of button text, can be "inset" or "raised" or false.
    $sb-text-style: "inset" !default

    // Gradient style of button, can be "flat", "glass", "matte", "shiny", or "simple".
    $sb-gradient-style: "simple" !default

    // Automatically generate pseudo state styles.
    $sb-pseudo-states: true !default

    // Add gradient png for IE 7+
    $sb-ie-support: false !default

    // Set the default Line height
    $sb-line-height: 1.5 !default

  # Sassy Button structure mixin that gets called every time you create a sassy button.
  # You can add any custom styles you want applied to all your buttons.
    @mixin sassy-button-default-sassytructure
    display: inline-block
    cursor: pointer
    line-height: $sb-line-height


Additional Sassy Button Styles
------------------------------

Sassy Buttons provides two mixins that can add a little extra style to your buttons

    # Mixin for adding styles to buttons
    @include sassy-button-style(style, color)

Available styles:

* Inset - box shadow that gives button an inset look.
* Push - box shadow that gives button a pushable look.


Available Sassy Buttons Mixins
------------------------------

A sassy button is made up a few different mixins, which all get called by the main sassy button mixin (@include sassy-button). These mixins are  available for you to use for advanced control over your buttons.

    # Mixin for the structure of your button (this mixin calls the sassy-button-default structure mixin).
    # Example use: Creating a custom button structure to apply color and text style mixins on.
    @include sassy-button-structure(border-radius, font-size, padding)

    # Mixin for the gradient styles
    # Example use: You could call this mixin on a :hover or :active state to provide your own styles for those pseudo states.
    @include sassy-button-gradient(gradient-style, first-color, second-color, text-color, text-style, auto-states)
