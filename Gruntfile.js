module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concat: {
            options: {
                separator: ';'
            },
            dist: {
                src: [
                    'bower_components/jquery/dist/jquery.js',
                    'bower_components/jquery-mousewheel/jquery.mousewheel.js',
                    'bower_components/raphael/raphael.js',
                    'bower_components/neveldo/jQuery-Mapael/js/jquery.mapael.js',
                    'bower_components/neveldo/jQuery-Mapael/js/maps/world_countries.js',
                    'js/*.js'
                ],
                dest: 'dist/js.js'
            }
        },
        uglify: {
            build: {
                src: 'dist/js.js',
                dest: 'dist/js.min.js'
            }
        },
        watch: {
            options: { livereload: true },
            scripts: {
                files: ['js/*.js'],
                tasks: ['concat', 'uglify'],
                options: {
                    spawn: false
                }
            },
            json: {
                files: ['json/*.json'],
                tasks: ['json', 'concat', 'uglify'],
                options: {
                    spawn: false
                }
            },
            css: {
                files: ['scss/*.scss'],
                tasks: ['compass:watch', 'json', 'concat', 'uglify'],
                options: {
                    spawn: false
                }
            }
        },
        compass: {
            watch: {
                options: {
                    config: 'config.rb',
                    require: [
                        'fileutils','SassyExport'
                        ]
                }
            },
            dist: {
                options: {
                    sassDir: 'scss',
                    cssDir: 'dist'
                }
            }
        },
        json: {
            dist: {
                options: {
                    namespace: 'MapaelMapConfig',
                    includePath: false,
                    processName: function(filename) {
                        return filename;
                    }
                },
                src: ['json/*.json'],
                dest: 'js/MapaelMapConfig.js'
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-concat');

    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.loadNpmTasks('grunt-contrib-compass');

    grunt.loadNpmTasks('grunt-json');

    grunt.registerTask('default', ['compass', 'json', 'concat', 'uglify', 'watch']);

};