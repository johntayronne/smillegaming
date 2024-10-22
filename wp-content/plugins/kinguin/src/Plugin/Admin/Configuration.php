<?php
/**
 * Common configuration trait.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Admin;

defined( 'ABSPATH' ) || exit;

trait Configuration {

	/**
	 * Get user provided Kinguin API key.
	 *
	 * @return string
	 */
	public function get_api_key() : string {
		return get_option( 'kinguin_api_key', '' );
	}



	/**
	 * Get current connection status with Kinguin API.
	 *
	 * @return bool
	 */
	public function get_connection_status() : bool {
		return get_option( 'kinguin_connection_status', false );
	}


	/**
	 * Get integration working environment.
	 *
	 * @return string
	 */
	public function get_environment() : string {
		return get_option( 'kinguin_environment', 'production' );
	}



	/**
	 * Get Kinguin API url.
	 *
	 * @return string
	 */
	public function get_api_url() : string {
		if ( 'production' === $this->get_environment() ) {
			return 'https://gateway.kinguin.net/esa/api';
		} else {
			return 'https://gateway.sandbox.kinguin.net/esa/api';
		}
	}



	/**
	 * Get automatic product sync frequency.
	 *
	 * @return string
	 */
	public function get_cron_frequency() : string {
		return get_option( 'kinguin_cron_frequency', 'none' );
	}



	/**
	 * Get order complete and order status change webhook secret.
	 *
	 * @return string
	 */
	public function get_orders_webhook_secret() : string {
		$secret = get_option( 'kinguin_orders_webhook_secret', false );
		if ( $secret ) {
			return $secret;
		} else {
			return $this->secret_generator();
		}
	}



	/**
	 * Get products update webhook secret.
	 *
	 * @return string
	 */
	public function get_products_webhook_secret() : string {
		$secret = get_option( 'kinguin_products_webhook_secret', false );
		if ( $secret ) {
			return $secret;
		} else {
			return $this->secret_generator();
		}
	}



	/**
	 * Get email message send with games keys.
	 *
	 * @return string
	 */
	public function get_email_message() : string {
		return get_option( 'kinguin_email_message', '' );
	}



	/**
	 * Get path to cache dir.
	 *
	 * @return string wp-content/uploads/kinguin
	 */
	public function get_cache_dir() : string {
		return wp_get_upload_dir()['basedir'] . '/kinguin/';
	}



	/**
	 * Get url to cache dir.
	 *
	 * @return string
	 */
	public function get_cache_url() : string {
		return wp_get_upload_dir()['baseurl'] . '/kinguin/';
	}



	/**
	 * Get latest currency rate.
	 *
	 * @param string $currency Currency three letters code (ISO 4217).
	 *
	 * @return float
	 */
	public function get_currency_rate( string $currency ) : float {
		try{
            return ( new CurrencyExchange() )->get_currency_rate( $currency );
        } catch ( \Exception $e ) {
            \wc_get_logger()->debug( 'Kinguin error get_currency_rate: ', array( 'source' => 'kinguin-debug-log' ) );
            \wc_get_logger()->debug( print_r( $e->getMessage(), true), array( 'source' => 'kinguin-debug-log' ) );
        }
	}



	/**
	 * Get region.
	 *
	 * @param int|null $region_id Region name or regions array if region_id is not provided.
	 *
	 * @return array|string
	 *
	 * @see https://github.com/kinguinltdhk/Kinguin-eCommerce-API/blob/master/api/products/v1/README.md#regions
	 */
	public function get_region( int $region_id = null ) {
        $region = array(
            1  => __( 'Europe', 'kinguin' ),
            2  => __( 'United States', 'kinguin' ),
            3  => __( 'Region free', 'kinguin' ),
            4  => __( 'Other', 'kinguin' ),
            5  => __( 'Outside Europe', 'kinguin' ),
            6  => __( 'RU VPN', 'kinguin' ),
            7  => __( 'Russia', 'kinguin' ),
            8  => __( 'United Kingdom', 'kinguin' ),
            9  => __( 'China', 'kinguin' ),
            10 => __( 'RoW (Rest of World)', 'kinguin' ),
            11 => __( 'Latin America', 'kinguin' ),
            12 => __( 'Asia', 'kinguin' ),
            13 => __( 'Germany', 'kinguin' ),
            14 => __( 'Australia', 'kinguin' ),
            15 => __( 'Brazil', 'kinguin' ),
            16 => __( 'India', 'kinguin' ),
            17 => __( 'Japan', 'kinguin' ),
            18 => __( 'North America', 'kinguin' ),
        );

		if ( $region_id ) {
			return $region[ $region_id ];
		} else {
			return $region;
		}
	}



	/**
	 * Generate 25 chars secret for the webhooks.
	 *
	 * @return string
	 */
	public function secret_generator() : string {
		$chars  = array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z' );
		$secret = '';
		$length = 25;
		for( $i = 0; $i < $length; $i++ ){
			$secret .= $chars[ rand( 0, ( count( $chars ) - 1 ) ) ];
		}
		return $secret;
	}


    /**
     * Get value of margin.
     *
     * @return string
     */
    public function get_margin_value() : string {
        return get_option( 'kinguin_product_margin_val', '' );
    }


    /**
     * Get type of margin.
     *
     * @return array
     */
    public function get_margin_type() : array {
        return get_option( 'kinguin_product_margin', array() );
    }


    /**
     * Get discount code.
     *
     * @return string
     */
    public function get_discount_code() : string {
        return get_option( 'kinguin_discount_code', '' );
    }


    /**
     * Get regions.
     *
     * @return array
     */
    public function get_regions() : array {
        return array(
            '1'  => 'Europe',
            '2'  => 'United States',
            '3'  => 'Region free',
            '4'  => 'Other',
            '5'  => 'Outside Europe',
            '6'  => 'RU VPN',
            '7'  => 'Russia',
            '8'  => 'United Kingdom',
            '9'  => 'China',
            '10' => 'RoW (Rest of World)',
            '11' => 'Latin America',
            '12' => 'Asia',
            '13' => 'Germany',
            '14' => 'Australia',
            '15' => 'Brazil',
            '16' => 'India',
            '17' => 'Japan',
            '18' => 'North America',
            );
    }


    /**
     * Get filter tags.
     *
     * @return array
     */
    public function get_filter_tags() : array {
        return array(
            '1'  => 'indie valley',
            '2'  => 'dlc',
            '3'  => 'base',
            '4'  => 'software',
            '5'  => 'prepaid',
        );
    }


    /**
     * Get filter languages.
     *
     * @return array
     */
    public function get_filter_languages() : array {
        return array(
            '1'   => 'German',
            '2'   => 'English',
            '3'   => 'French',
            '4'   => 'Spanish',
            '5'   => 'Japanese',
            '6'   => 'Russian',
            '7'   => 'Chinese',
            '8'   => 'Korean',
            '9'   => 'Italian',
            '10'  => 'Polish',
            '11'  => 'Portuguese',
            '12'  => 'Czech',
            '13'  => 'Danish',
            '14'  => 'Dutch',
            '15'  => 'Hungarian',
            '16'  => 'Swedish',
            '17'  => 'Bulgarian',
            '18'  => 'Finnish',
            '19'  => 'Norwegian',
            '20'  => 'Greek',
            '21'  => 'Turkish',
            '22'  => 'Arabic',
            '23'  => 'Portuguese - Brazil',
            '24'  => 'Romanian',
        );
    }


    /**
     * Get filter platforms.
     *
     * @return array
     */
    public function get_platforms() : array {
        return array(
            '1'   => 'EA Origin',
            '2'   => 'Steam',
            '3'   => 'Battle.net',
            '4'   => 'NCSoft',
            '5'   => 'Uplay',
            '6'   => 'Kinguin',
            '7'   => 'XBOX 360',
            '8'   => 'PlayStation 3',
            '9'   => 'XBOX ONE',
            '10'  => 'PlayStation 4',
            '11'  => 'Android',
            '12'  => 'PlayStation Vita',
            '13'  => 'GOG COM',
            '14'  => 'Nintendo',
            '15'  => 'Epic Games',
            '16'  => 'PlayStation 5',
            '17'  => 'XBOX Series X|S',
            '18'  => 'Bethesda',
            '19'  => 'Rockstar Games',
            '20'  => 'Mog Station',
        );
    }



    /**
     * Get filter merchants.
     *
     * @return array
     */
    public function get_merchants() : array {
        return array(
            '1'   => 'LIMITED QUANTITY',
            '2'   => 'wildboy',
            '3'   => 'Global Games',
            '4'   => 'Have Fun Store',
            '5'   => 'GameDock',
            '6'   => 'HoGames',
            '7'   => 'IHM GAMES',
            '8'   => 'trusted seller',
            '9'   => 'RavenousKeys',
            '10'  => 'LuckyPicker',
            '11'  => 'SwiftKeyz',
            '12'  => 'Worldofcdkeys',
            '13'  => 'KeyStream',
            '14'  => 'USA Software & Games',
            '15'  => 'Europe Digital Keys',
            '16'  => 'iN-Net',
            '17'  => 'LGK-Store',
            '18'  => 'GamingWorld',
            '19'  => 'Chain Breaker',
            '20'  => 'MonKeys',
            '21'  => 'GameCrew',
            '22'  => 'RoyWin4me',
            '23'  => 'Games & Chill',
            '24'  => 'BuyFromMePlease',
        );
    }



    /**
     * Get import switch status.
     *
     * @return bool
     */
    public function is_webhook_import_enabled() : bool {
        return get_option( 'kinguin_enable_webhook_import', false );
    }


    /**
     * Get genres.
     *
     * @return array
     */
    public function get_genres() : array {
        return array(
            '1'  => 'Action',
            '2'  => 'Adventure',
            '3'  => 'Anime',
            '4'  => 'Casual',
            '5'  => 'Co-op',
            '6'  => 'Dating Simulator',
            '7'  => 'Fighting',
            '8'  => 'FPS',
            '9'  => 'Hack and Slash',
            '10' => 'Hidden Object',
            '11' => 'Horror',
            '12' => 'Indie',
            '13' => 'Life Simulation',
            '14' => 'MMO',
            '15' => 'Music / Soundtrack',
            '16' => 'Online Courses',
            '17' => 'Open World',
            '18' => 'Platformer',
            '19' => 'Point & click',
            '20' => 'PSN Card',
            '21' => 'Puzzle',
            '22' => 'Racing',
            '23' => 'RPG',
            '24' => 'Simulation',
            '25' => 'Software',
            '26' => 'Sport',
            '27' => 'Story rich',
            '28' => 'Strategy',
            '29' => 'Subscription',
            '30' => 'Survival',
            '31' => 'Third-Person Shooter',
            '32' => 'Visual Novel',
            '33' => 'VR Games',
            '34' => 'XBOX LIVE Gold Card',
            '35' => 'XBOX LIVE Points',
            '36' => 'Adult Games',
        );
    }


    /**
     * Get filter preset.
     *
     */
    public function get_filter_preset() {
        $filter = get_option( 'kinguin_settings_import' );
        if( !empty($filter) ) {
            $filter_param = '';

            foreach ( $filter as $param => $value) {
                if(!is_array($value)) {
                    if(!empty($value)) {
                        $filter_param .= '&' . $param . '=' . $value;
                    }

                } else {
                    if(count($value) == 1) {
                        $filter_param .= '&' . $param . '=' . reset($value);
                    } else {
                        $string_param = implode(',', $value);
                        $filter_param .= '&' . $param . '=' . $string_param;
                    }
                }
            }
            return $filter_param;
        }
        return false;
    }


    /**
     * Get PC platforms.
     *
     * @return array
     */
    public function get_pc_platforms() : array {
        return array(
            'EA Origin'      => 'ea-origin',
            'Steam'          => 'steam',
            'Battle.net'     => 'battle-net',
            'NCSoft'         => 'ncsoft',
            'Uplay'          => 'uplay',
            'GOG COM'        => 'gog-com',
            'Epic Games'     => 'epic-games',
            'Bethesda'       => 'bethesda',
            'Rockstar Games' => 'rockstar-games',
            'Mog Station'    => 'mog-station',
        );
    }


    /**
     * Get default categories to create during insert new products.
     *
     * @return array
     */
    public function get_default_categories() : array {
        return array(
            'XBOX 360'         => 'xbox-360-platform',
            'XBOX ONE'         => 'xbox-one',
            'XBOX Series X|S'  => 'xbox-series-xs',
            'PlayStation 3'    => 'playstation-3-platform',
            'PlayStation 4'    => 'playstation-4-platform',
            'PlayStation 5'    => 'playstation-5-platform',
            'PlayStation Vita' => 'vita-playstation',
            'Android'          => 'android',
            'Nintendo'         => 'nintendo',
            'Prepaid'          => 'prepaid',
            'Software'         => 'software',
            'PC'               => 'pc-platform',
            'Others'           => 'others-platforms'
        );
    }

    /**
     * Helper function to get correct names of some categories for frontend.
     *
     * @return array
     */
    public function rename_genre_for_category() : array {
        return array(
            'Co-op'                => 'Cooperation',
            'Fighting'             => 'Fight',
            'Hidden Object'        => 'Hidden items',
            'Puzzle'               => 'Logic',
            'Story rich'           => 'Narrative',
            'Third-Person Shooter' => 'TPS',
            'Music / Soundtrack'   => 'Soundtrack',
            'Life Simulation'      => 'Life',
            'Dating Simulator'     => 'Dating',
        );
    }

    /**
     * Helper function to get slugs for categories based on genres
     *
     * @return array
     */
    public function get_genre_slug() : array {
        return array(
            'Action'               => 'action',
            'Adventure'            => 'adventure',
            'Anime'                => 'anime',
            'Casual'               => 'casual',
            'Co-op'                => 'cooperation',
            'Dating Simulator'     => 'dating',
            'Fighting'             => 'fighting',
            'FPS'                  => 'fps',
            'Hack and Slash'       => 'hack-and-slash',
            'Hidden Object'        => 'hidden-items',
            'Horror'               => 'horror',
            'Indie'                => 'indie',
            'Open World'           => 'open-world',
            'Platformer'           => 'platformer',
            'Point & click'        => 'point-and-click',
            'PSN Card'             => 'psn-card',
            'Puzzle'               => 'logic',
            'Racing'               => 'racing',
            'RPG'                  => 'rpg',
            'Simulation'           => 'simulation',
            'Life Simulation'      => 'life-simulation',
            'Software'             => 'subcat-software',
            'Sport'                => 'sport',
            'Story rich'           => 'narrative',
            'Strategy'             => 'strategy',
            'Survival'             => 'survival',
            'XBOX LIVE Gold Card'  => 'xbox-live-gold-card',
            'Third-Person Shooter' => 'tps',
            'MMO'                  => 'mmo',
            'Music / Soundtrack'   => 'soundtrack',
            'Visual Novel'         => 'visual-novel',
            'VR Games'             => 'vr-games',
            'Subscription'         => 'subscription',
            'Adult Games'          => 'adult-games',
            'Online Courses'       => 'online-courses',
        );
    }

}
