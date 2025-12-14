/* jshint node:true */
module.exports = function( grunt ){
	'use strict';

	grunt.initConfig({
		// setting folder templates
		dirs: {
			css: 'assets/css',
			less: 'assets/css',
			js: 'assets/js'
		},

		// Compile all .less files.
		less: {
			compile: {
				options: {
					// These paths are searched for @imports
					paths: ['<%= less.css %>/']
				},
				files: [{
					expand: true,
					cwd: '<%= dirs.css %>/',
					src: [
						'*.less',
						'!mixins.less'
					],
					dest: '<%= dirs.css %>/',
					ext: '.css'
				}]
			}
		},

		// Minify all .css files.
		cssmin: {
			minify: {
				expand: true,
				cwd: '<%= dirs.css %>/',
				src: ['*.css'],
				dest: '<%= dirs.css %>/',
				ext: '.css'
			}
		},

		// Minify .js files.
		uglify: {
			options: {
				preserveComments: 'some'
			},
			jsfiles: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/',
					src: [
						'*.js',
						'!*.min.js',
						'!Gruntfile.js',
					],
					dest: '<%= dirs.js %>/',
					ext: '.min.js'
				}]
			}
		},

		// Watch changes for assets
		watch: {
			less: {
				files: [
					'<%= dirs.less %>/*.less',
				],
				tasks: ['less', 'cssmin'],
			},
			js: {
				files: [
					'<%= dirs.js %>/*js',
					'!<%= dirs.js %>/*.min.js'
				],
				tasks: ['uglify']
			}
		},

		// Create ZIP package
		compress: {
			main: {
				options: {
					archive: 'wp-booking-system-v1.0.0.zip',
					mode: 'zip'
				},
				files: [
					{
						expand: true,
						cwd: 'build/wp-booking-system/',
						src: ['**/*'],
						dest: 'wp-booking-system/'
					}
				]
			}
		},

		// Shell command to run build script
		shell: {
			buildZip: {
				command: function() {
					// Detect OS and run appropriate build script with cleanup flag
					// This ensures consistent behavior: build directory is removed after ZIP creation
					if (process.platform === 'win32') {
						return 'powershell -ExecutionPolicy Bypass -File build-plugin.ps1 -Cleanup';
					} else {
						return 'bash build-plugin-unix.sh --cleanup';
					}
				},
				options: {
					stdout: true,
					stderr: true,
					failOnError: true
				}
			}
		},

	});

	// Load NPM tasks to be used here
	grunt.loadNpmTasks( 'grunt-contrib-less' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-contrib-compress' );
	grunt.loadNpmTasks( 'grunt-shell' );

	// Register tasks
	grunt.registerTask( 'default', [
		'less',
		'cssmin',
		'uglify'
	]);

	// Build task: minify assets and create ZIP
	grunt.registerTask( 'build', [
		'less',
		'cssmin',
		'uglify',
		'shell:buildZip'
	]);

	// Package task: create ZIP (assumes build directory exists)
	grunt.registerTask( 'package', [
		'compress'
	]);

};