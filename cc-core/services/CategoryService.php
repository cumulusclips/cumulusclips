<?php

class CategoryService extends ServiceAbstract
{
    /**
     * Retrieve list of active categories
     * @return array Returns a list with the category Ids of active categories 
     */
    public function getCategories()
    {
        $categoryMapper = new CategoryMapper();
        $categories = $categoryMapper->getMultipleCategoriesByCustom(array('true' => true));
        $categoryList = array();
        foreach ($categories as $category) {
            $categoryList[$category->categoryId] = $category;
        }
        return $categoryList;
    }
}