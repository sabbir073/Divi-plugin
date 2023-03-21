<?php
if (!class_exists("Screenideaz_import")) {
    require_once ABSPATH . "wp-admin/includes/file.php";
    class Screenideaz_import {
        public function post_import($json) {
            if (isset($json["data"])) {
                if (!empty($json["images"])) {
                    $json["images"] = $this->images_upload($json["images"]);
                }
                foreach ($json["data"] as $key => $scelement) {
                    $sc_title = $scelement["post_title"];
                    $sc_content = $this->images_content_replace($scelement["post_content"], $json["images"]);
                    $sc_post_data = ["post_title" => $sc_title, "post_content" => $sc_content, "post_status" => "publish", "post_type" => "et_pb_layout", ];
                    $sc_post_id = wp_insert_post($sc_post_data);
                    //post meta
                    if (isset($sc_post_id) && "" !== $sc_post_id) {
                        //Set Post Meta
                        if (isset($scelement["post_meta"]) && is_array($scelement["post_meta"])) {
                            foreach ($scelement["post_meta"] as $meta_key => $meta_value) {
                                $meta_key = sanitize_text_field($meta_key);
                                if (count($meta_value) < 2) {
                                    $meta_value = wp_kses_post($meta_value[0]);
                                } else {
                                    $meta_value = array_map("wp_kses_post", $meta_value);
                                }
                                update_post_meta($sc_post_id, $meta_key, $meta_value);
                            }
                        }
                        //Set Terms
                        if (isset($scelement["terms"]) && is_array($scelement["terms"])) {
                            $sc_processed_terms = [];
                            foreach ($scelement["terms"] as $term) {
                                if (empty($term["parent"])) {
                                    $parent = 0;
                                } else {
                                    if (isset($term["all_parents"]) && !empty($term["all_parents"])) {
                                        $this->parent_category_sc_restore($term["all_parents"], $term["taxonomy"]);
                                    }
                                    $parent = term_exists($term["parent"], $term["taxonomy"]);
                                    if (is_array($parent)) {
                                        $parent = $parent["term_id"];
                                    }
                                }
                                if (!($insert = term_exists($term["slug"], $term["taxonomy"]))) {
                                    $insert = wp_insert_term($term["name"], $term["taxonomy"], ["slug" => $term["slug"], "description" => $term["description"], "parent" => intval($parent), ]);
                                }
                                if (is_array($insert) && !is_wp_error($insert)) {
                                    $sc_processed_terms[$term["taxonomy"]][] = $term["slug"];
                                }
                            }
                            // Set post terms.
                            foreach ($sc_processed_terms as $taxonomy => $ids) {
                                wp_set_object_terms($sc_post_id, $ids, $taxonomy);
                            }
                        }
                       
                        //Set Presets
                        if (isset($scelement["presets"]) && is_array($scelement["presets"])) {

                            $this->import_sc_presets_global($scelement["presets"]);
                            
                        }
                        //Set Global Color
                        if (isset($scelement["global_colors"]) && is_array($scelement["global_colors"])) {
                            $this->sc_global_import_colors($scelement["global_colors"]);
                        }
                        return true;
                        break;
                    }
                }
            }
        }
        public function images_upload($scimages) {
            $filesystem = $this->getfilesystem();
            foreach ($scimages as $key => $image) {
                $namebase = sanitize_file_name(wp_basename($image["url"]));
                $medias = get_posts(["posts_per_page" => - 1, "post_type" => "attachment", "meta_key" => "_wp_attached_file", "meta_value" => pathinfo($namebase, PATHINFO_FILENAME), "meta_compare" => "LIKE", ]);
                $scid = 0;
                $scurl = "";
                // checking duplicates
                if (!is_wp_error($medias) && !empty($medias)) {
                    foreach ($medias as $media) {
                        $media_url = wp_get_attachment_url($media->ID);
                        $scfile = get_attached_file($media->ID);
                        $scfilename = sanitize_file_name(wp_basename($scfile));
                        // Use existing image only if the content matches.
                        if ($filesystem->get_contents($scfile) === base64_decode($image["encoded"])) {
                            $scid = isset($image["id"]) ? $media->ID : 0;
                            $scurl = $media_url;
                            break;
                        }
                    }
                }
                //new image.
                if (empty($scurl)) {
                    $temp_file = wp_tempnam();
                    $filesystem->put_contents($temp_file, base64_decode($image["encoded"]));
                    $filetype = wp_check_filetype_and_ext($temp_file, $namebase);
                    // Avoid further duplicates if the proper_file name match an existing image.
                    if (isset($filetype["proper_filename"]) && $filetype["proper_filename"] !== $namebase) {
                        if (isset($scfilename) && $scfilename === $filetype["proper_filename"]) {
                            // Use existing image only if the basenames and content match.
                            if ($filesystem->get_contents($scfile) === $filesystem->get_contents($temp_file)) {
                                $filesystem->delete($temp_file);
                                continue;
                            }
                        }
                    }
                    $file = ["name" => $namebase, "tmp_name" => $temp_file, ];
                    require_once ABSPATH . "wp-admin/includes/media.php";
                    require_once ABSPATH . "wp-admin/includes/file.php";
                    require_once ABSPATH . "wp-admin/includes/image.php";
                    $upload = media_handle_sideload($file, 0);
                    if (!is_wp_error($upload)) {
                        // Set the replacement as an id if the original image was set as an id (for gallery).
                        $scid = isset($image["id"]) ? $upload : 0;
                        $scurl = wp_get_attachment_url($upload);
                    } else {
                        // Make sure the temporary file is removed if media_handle_sideload didn't take care of it.
                        $filesystem->delete($temp_file);
                    }
                }
                // Only declare the replace if a url is set.
                if ($scid > 0) {
                    $scimages[$key]["replacement_id"] = $scid;
                }
                if (!empty($url)) {
                    $scimages[$key]["replacement_url"] = $scurl;
                }
                unset($scurl);
            }
            return $scimages;
        }
        protected function getfilesystem() {
            static $filesystem = null;
            if (null === $filesystem) {
                $filesystem = $this->setfilesystem();
            }
            return $filesystem;
        }
        protected function setfilesystem() {
            global $wp_filesystem;
            add_filter("filesystem_method", [$this, "replacefilesystemmethod"]);
            WP_Filesystem();
            return $wp_filesystem;
        }
        public function replacefilesystemmethod() {
            return "direct";
        }
        public function images_content_replace($sccontent, $scimages) {
            foreach ($scimages as $key => $image) {
                if (isset($image["replacement_id"]) && isset($image["id"])) {
                    $sc_search = $image["id"];
                    $sc_replacement = $image["replacement_id"];
                    $sccontent = preg_replace("/(gallery_ids=.*){$sc_search}(.*\")/", "\${1}{$sc_replacement}\${2}", $sccontent);
                }
                if (isset($image["url"]) && isset($image["replacement_url"])) {
                    $sccontent = str_replace($image["url"], $image["replacement_url"], $sccontent);
                }
            }
            return $sccontent;
        }
        public function parent_category_sc_restore($parents_array, $taxonomy) {
            foreach ($parents_array as $slug => $category_data) {
                $current_category = term_exists($slug, $taxonomy);
                if (!is_array($current_category)) {
                    $parent_id = 0 !== $category_data["parent"] ? term_exists($category_data["parent"], $taxonomy) : 0;
                    wp_insert_term($category_data["name"], $taxonomy, ["slug" => $slug, "description" => $category_data["description"], "parent" => is_array($parent_id) ? $parent_id["term_id"] : $parent_id, ]);
                } elseif ((!isset($current_category["parent"]) || 0 === $current_category["parent"]) && 0 !== $category_data["parent"]) {
                    $parent_id = 0 !== $category_data["parent"] ? term_exists($category_data["parent"], $taxonomy) : 0;
                    wp_update_term($current_category["term_id"], $taxonomy, ["parent" => is_array($parent_id) ? $parent_id["term_id"] : $parent_id, ]);
                }
            }
        }
        public function import_sc_presets_global( $sc_presets ) {
			if ( ! is_array( $sc_presets ) ) {
				return false;
			}

			$sc_all_modules            = ET_Builder_Element::get_modules();
			$sc_module_presets_manager = ET_Builder_Global_Presets_Settings::instance();
			$sc_global_presets         = $sc_module_presets_manager->get_global_presets();
			$sc_presets_to_import      = array();

			foreach ( $sc_presets as $sc_module_type => $sc_module_presets ) {
				$sc_presets_to_import[ $sc_module_type ] = array(
					'presets' => array(),
				);

				if ( ! isset( $sc_global_presets->$sc_module_type->presets ) ) {
					$initial_preset_structure = ET_Builder_Global_Presets_Settings::generate_module_initial_presets_structure( $sc_module_type, $sc_all_modules );

					$sc_global_presets->$sc_module_type = $initial_preset_structure;
				}

				$local_presets      = $sc_global_presets->$sc_module_type->presets;
				$local_preset_names = array();

				foreach ( $local_presets as $preset ) {
					array_push( $local_preset_names, $preset->name );
				}

				foreach ( $sc_module_presets['presets'] as $preset_id => $preset ) {
					$imported_name = sanitize_text_field( $preset['name'] );
					$name          = in_array( $imported_name, $local_preset_names )
						? $imported_name . ' ' . esc_html__( 'imported', 'et-core' )
						: $imported_name;

					$sc_presets_to_import[ $sc_module_type ]['presets'][ $preset_id ] = array(
						'name'     => $name,
						'created'  => time() * 1000,
						'updated'  => time() * 1000,
						'version'  => $preset['version'],
						'settings' => $preset['settings'],
					);
				}
			}

			// Merge existing Global Presets with imported ones
			foreach ( $sc_presets_to_import as $sc_module_type => $sc_module_presets ) {
				foreach ( $sc_module_presets['presets'] as $preset_id => $preset ) {
					$sc_global_presets->$sc_module_type->presets->$preset_id           = (object) array();
					$sc_global_presets->$sc_module_type->presets->$preset_id->name     = sanitize_text_field( $preset['name'] );
					$sc_global_presets->$sc_module_type->presets->$preset_id->created  = $preset['created'];
					$sc_global_presets->$sc_module_type->presets->$preset_id->updated  = $preset['updated'];
					$sc_global_presets->$sc_module_type->presets->$preset_id->version  = $preset['version'];
					$sc_global_presets->$sc_module_type->presets->$preset_id->settings = (object) array();

					foreach ( $preset['settings'] as $setting_name => $value ) {
						$setting_name_sanitized = sanitize_text_field( $setting_name );
						$value_sanitized        = sanitize_text_field( $value );

						$sc_global_presets->$sc_module_type->presets->$preset_id->settings->$setting_name_sanitized = $value_sanitized;
					}

					// Inject Global colors into imported presets.
					$sc_global_presets->$sc_module_type->presets->$preset_id->settings = ET_Builder_Global_Presets_Settings::maybe_set_global_colors( $sc_global_presets->$sc_module_type->presets->$preset_id->settings );
				}
			}

			et_update_option( ET_Builder_Global_Presets_Settings::GLOBAL_PRESETS_OPTION, $sc_global_presets );

			$sc_global_presets_history = ET_Builder_Global_Presets_History::instance();
			$sc_global_presets_history->add_global_history_record( $sc_global_presets );

			return true;
		}
        public function sc_global_import_colors($sc_incoming_global_colors) {
            $sc_global_colors = [];
            foreach ($sc_incoming_global_colors as $sc_incoming_gcolor) {
                $key = et_()->sanitize_text_fields($sc_incoming_gcolor[0]);
                $sc_global_colors[$key] = et_()->sanitize_text_fields($sc_incoming_gcolor[1]);
            }
            $stored_global_colors = et_builder_get_all_global_colors();
            if (!empty($stored_global_colors)) {
                $sc_global_colors = array_merge($sc_global_colors, $stored_global_colors);
            }
            et_update_option("et_global_colors", $sc_global_colors);
        }
    }
}
