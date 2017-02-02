<?php
namespace Blaze\WP;

/**
 * Post object
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Post
{
	public $data = array();

	public $id;
	public $isSinglePage;
	public $title;
	public $body;
	public $excerpt;
	public $permalink;
	public $author;

	// Thumbnails
	public $hasThumbnail;
	public $thumbnailURL;

	// DateTime
	public $gmtTimestamp;
	public $date;

	// Categories
	public $hasCategories;
	private $categoryLimit; // Default is null: output all categories
	private $categorySeparator; // Default value: ','

	// Tags
	public $tagData;
	public $hasTags;
	private $tagLimit; // Default is null: output all tags
	private $tagSeparator; // Default value: ','

	// Post number in a WP Query sequence
	public $sequenceNum;
	public $isOdd;
	public $isEven;
	public $is1st;
	public $is2nd;
	public $is3rd;
	public $is4th;
	public $is5th;
	public $is6th;
	public $is7th;
	public $is8th;
	public $is9th;
	public $is10th;

	public function __construct()
	{
		// Category constants
		$this->categoryLimit = null;
		$this->categorySeparator = ',';

		// Tag constants
		$this->tagLimit = null;
		$this->tagSeparator = ',';

	}

	public function dateAgo()
	{
		$post_time = get_post_time('G', true);
		$time_diff = time() - $post_time;
		if ($time_diff < 60) {
			$html = __('Just now');
		} else {
			$html = sprintf(__('%s ago'), human_time_diff($post_time));
		}
		return $html;
	}

	/**
	 * This is a default getData method.
	 * It retrieves primary, category and tags data.
	 * Extend it if you want a different functionality by default.
	 */
	public function getData() {
		$this->getPrimaryData();
		$this->getCategoryData();
		$this->getTagData();
	}

	/**
	 * Retrieves primary post data.
	 * Does not result in any DB queries.
	 */
	public function getPrimaryData()
	{
		$this->id = get_the_ID();
		$this->permalink = get_permalink();
		$this->isSinglePage = is_page() || is_single();
		$this->title = get_the_title();
		$body = get_the_content();
		// get_the_content() does not run trough 'the_content' filter that expands shortcodes and embeds videos.
		// That's why we have to run this filter manually.
		$this->body = apply_filters('the_content', $body);

		$this->excerpt = get_the_excerpt();
		$this->author = get_the_author();
		$this->gmtTimestamp = get_post_time('U', true);
		$this->date = get_the_date();

		$this->hasThumbnail = has_post_thumbnail();
		if ($this->hasThumbnail)
			$this->thumbnailURL = wp_get_attachment_url(get_post_thumbnail_id($this->id));
	}

	/**
	 * Retrieves category-related data.
	 * Usage cost: 2 DB Queries
	 */
	public function getCategoryData() {
		$this->hasCategories = has_category();  // Cost 2Q
	}

	/**
	 * Retrieves tag-related data.
	 * Usage cost: 2 DB Queries
	 */
	public function getTagData() {
		$this->tagData =  get_the_tags(); // Cost: 2Q
		$this->hasTags = is_array($this->tagData) AND count($this->tagData)>0? true: false;
	}

	public function staticData() {
		$this->data['id'] = $this->id;
		$this->data['permalink'] = $this->permalink;
		$this->data['isSinglePage'] = $this->isSinglePage;
		$this->data['title'] = $this->title;
		$this->data['body'] = $this->body;
		$this->data['excerpt'] = $this->excerpt;
		$this->data['author'] = $this->author;
		$this->data['gmtTimestamp'] = $this->gmtTimestamp;
		$this->data['date'] = $this->date;

		$this->data['hasThumbnail'] = $this->hasThumbnail;
		$this->data['thumbnailURL'] = $this->thumbnailURL;

		$this->data['hasCategories'] = $this->hasCategories;

		$this->data['tagData'] = $this->tagData;
		$this->data['hasTags'] = $this->hasTags;

		$this->data['sequenceNum'] = $this->sequenceNum;
		$this->data['isOdd'] = $this->isOdd;
		$this->data['isEven'] = $this->isEven;

		$this->data['categoryLinks'] = $this->categoryLinks();
		$this->data['tagLinks'] = $this->tagLinks();

		return $this->data;
	}

	public function setSequenceNum($num)
	{
		$this->sequenceNum = $num;
		switch ($num) {
			case 1:
				$this->is1st = true;
				$this->data['is1st'] = $this->is1st;
				break;
			case 2:
				$this->is2nd = true;
				$this->data['is2nd'] = $this->is2nd;
				break;
			case 3:
				$this->is3rd = true;
				$this->data['is3rd'] = $this->is3rd;
				break;
			case 4:
				$this->is4th = true;
				$this->data['is4th'] = $this->is4th;
				break;
			case 5:
				$this->is5th = true;
				$this->data['is5th'] = $this->is5th;
				break;
			case 6:
				$this->is6th = true;
				$this->data['is6th'] = $this->is6th;
				break;
			case 7:
				$this->is7th = true;
				$this->data['is7th'] = $this->is7th;
				break;
			case 8:
				$this->is8th = true;
				$this->data['is8th'] = $this->is8th;
				break;
			case 9:
				$this->is9th = true;
				$this->data['is9th'] = $this->is9th;
				break;
			case 10:
				$this->is10th = true;
				$this->data['is10th'] = $this->is10th;
				break;
		}
		$this->isOdd = ($num % 2 == 0)? false: true;
		$this->isEven = ($num % 2 == 0)? true: false;
	}

	public function categoryLinks() {
		$count = 0;
		$html = '';

		if (!$this->hasCategories) return '';

		// For static data this method is called outside of the loop and post id needs to be provided
		$categoryList = get_the_category($this->id);
		$categories = array_slice($categoryList, 0, $this->categoryLimit);
		foreach($categories as $category) {
			$sep = '';
			if ($count>0) $sep = $this->categorySeparator . " ";
			$categoryLink = get_category_link($category->term_id);
			$categoryName = esc_attr( $category->name );
			$html .= "{$sep}<a href='{$categoryLink}'>{$categoryName}</a>";
			$count++;
		}
		return $html;
	}

	public function tagLinks() {
		$count = 0;
		$html = '';

		if (!$this->hasTags) return '';

		$tags = array_slice($this->tagData, 0, $this->tagLimit);
		foreach($tags as $tag) {
			$sep = '';
			if ($count>0) $sep = $this->tagSeparator . " ";
			$tagLink = get_tag_link($tag->term_id);
			$tagName = esc_attr( $tag->name );
			$html .= "{$sep}<a href='{$tagLink}'>{$tagName}</a>";
			$count++;
		}
		return $html;
	}
}
