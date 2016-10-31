/*
 * Copyright 2015 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package com.aprivate.selfie.Home;

import android.content.ContentValues;
import android.content.Context;
import android.content.res.Resources;

import com.aprivate.selfie.R;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.util.ArrayList;
import java.util.List;

/**
 * Database for storing and retrieving info for categories and quizzes
 */
public class Database{

    private final Resources mResources;
    private static List<Category> CategoriesArray = new ArrayList<>();

    public Database(Context context) {
        //prevents external instance creation
        mResources = context.getResources();
    }

    public List<Category> GetfromJSON(Context context) throws IOException {
        try {
            StringBuilder categoriesJson = new StringBuilder();
            InputStream rawCategories = mResources.openRawResource(R.raw.categories);
            BufferedReader reader = new BufferedReader(new InputStreamReader(rawCategories));
            String line;
            while ((line = reader.readLine()) != null) {
                categoriesJson.append(line);
            }

            ContentValues values = new ContentValues(); // reduce, reuse
            JSONArray jsonArray =  new JSONArray(categoriesJson.toString());;

            JSONObject categoryjson;

            for (int i = 0; i <= jsonArray.length() - 1; i++) {
                categoryjson = jsonArray.getJSONObject(i);
                final String categoryId = categoryjson.getString(JsonAttributes.ID);
                Category category = LocalCreateCategory(values, categoryjson, categoryId);
                CategoriesArray.add(category);
            }

            return CategoriesArray;
        } catch (JSONException e) {
            e.printStackTrace();
            return null;
        }
    }

    private Category LocalCreateCategory(ContentValues values, JSONObject category,String categoryId) throws JSONException{
        values.clear();
        values.put(CategoryTable.COLUMN_ID, categoryId);
        values.put(CategoryTable.COLUMN_NAME, category.getString(JsonAttributes.NAME));
        values.put(CategoryTable.COLUMN_THEME, category.getString(JsonAttributes.THEME));
        final String id = values.get(CategoryTable.COLUMN_ID).toString();
        final String name = values.get(CategoryTable.COLUMN_NAME).toString();
        final String themeName = values.get(CategoryTable.COLUMN_THEME).toString();
        final Theme theme = Theme.valueOf(themeName);

        return new Category(name, id, theme);
    }

    public Category GetCategory(String categoryId){
        for (int i = 0; i <= CategoriesArray.size() - 1; i++) {
            if (CategoriesArray.get(i).getId().equals(categoryId)) {
                return CategoriesArray.get(i);
            }

        }
        return null;
    }
}
