# Module Bundler

**ARCHIVE PROJECT**

> CLI code asset bundler written in ```php``` originally developed in 2007 which automated the bundling of js and css along with image and other related assets.

---

I originally wrote this for an enterprise project which had a huge amount of js and css. At this time is was common to work with a small number of
monolithic js and css files as an attempt to reduce the number of http requests which made development and testing difficult. There were not many
options available to reliably automate bundling with post build steps such as minification. Module bundlers such as Browserify (c. 2011) and Webpack (c. 2012)
had yet to be written and Node.js was still a few years away.

Each discrete 'module' has it's dependencies defined in an associated XML file which was used during the build phase to create a depenancy graph which was then used
to ensure only 'required' js and css were included and combined in the correct order.

## Plugins

Plugins could be configured to perform additional work such as url resolution to locate dependancies, SASS plugin to process scss/sass files, linting js using
JSLint, etc. Custom plugins could also be written and used as part of the bundling.

## Development

In dev, a simple proxy was used to serve specially crafted generated js and css so that changes to the unline 'modules' could be immediately reflected in the
browser.

## Production

In production, additional plugins could be used to perform post-build work such as minification and other steps such as creating versioned tarball artifacts.
