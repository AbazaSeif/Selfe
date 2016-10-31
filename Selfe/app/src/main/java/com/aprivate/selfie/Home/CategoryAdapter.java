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

import android.app.Activity;
import android.content.res.Resources;
import android.databinding.DataBindingUtil;
import android.support.annotation.ColorRes;
import android.support.v4.content.ContextCompat;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.aprivate.selfie.R;
import com.aprivate.selfie.databinding.ItemCategoryBinding;

import java.io.IOException;
import java.util.List;

import de.hdodenhof.circleimageview.CircleImageView;

public class CategoryAdapter extends RecyclerView.Adapter<CategoryAdapter.ViewHolder> {

    public static final String DRAWABLE = "drawable";
    private static final String ICON_CATEGORY = "icon_category_";
    private final Resources mResources;
    private final String mPackageName;
    private final LayoutInflater mLayoutInflater;
    private final Activity mActivity;
    private List<Category> mCategories;

    private OnItemClickListener mOnItemClickListener;

    public interface OnItemClickListener {
        void onClick(View view, int position);
    }

    public CategoryAdapter(Activity activity) {
        mActivity = activity;
        mResources = mActivity.getResources();
        mPackageName = mActivity.getPackageName();
        mLayoutInflater = LayoutInflater.from(activity.getApplicationContext());
        updateCategories(activity);
    }

    @Override
    public ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return new ViewHolder((ItemCategoryBinding) DataBindingUtil
                .inflate(mLayoutInflater, R.layout.item_category, parent, false));
    }

    @Override
    public void onBindViewHolder(final ViewHolder holder, final int position) {
        ItemCategoryBinding binding = holder.getBinding();
        Category category = mCategories.get(position);
        binding.setCategory(category);
        binding.executePendingBindings();
        setCategoryIcon(category, binding.categoryIcon);
        holder.itemView.setBackgroundColor(getColor(category.getTheme().getWindowBackgroundColor()));
        binding.categoryTitle.setTextColor(getColor(category.getTheme().getTextPrimaryColor()));
        binding.categoryTitle.setBackgroundColor(getColor(category.getTheme().getPrimaryColor()));
        holder.itemView.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                mOnItemClickListener.onClick(v, holder.getAdapterPosition());
            }
        });
    }

    @Override
    public long getItemId(int position) {
        return mCategories.get(position).getId().hashCode();
    }

    @Override
    public int getItemCount() {
        return mCategories.size();
    }

    public Category getItem(int position) {
        return mCategories.get(position);
    }

    /**
     * @param id Id of changed category.
     * @see android.support.v7.widget.RecyclerView.Adapter#notifyItemChanged(int)
     */
    public final void notifyItemChanged(String id) {
        updateCategories(mActivity);
        notifyItemChanged(getItemPositionById(id));
    }

    private int getItemPositionById(String id) {
        for (int i = 0; i < mCategories.size(); i++) {
            if (mCategories.get(i).getId().equals(id)) {
                return i;
            }

        }
        return -1;
    }

    public void setOnItemClickListener(OnItemClickListener onItemClickListener) {
        mOnItemClickListener = onItemClickListener;
    }

    private void setCategoryIcon(Category category, CircleImageView icon) {
        final int categoryImageResource = mResources.getIdentifier(ICON_CATEGORY + category.getId(), DRAWABLE, mPackageName);
        icon.setImageResource(categoryImageResource);
    }

    private void updateCategories(Activity activity) {
        Database TDH = new Database(activity);
        try {
            mCategories = TDH.GetfromJSON(activity);
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    /**
     * Convenience method for color loading.
     *
     * @param colorRes The resource id of the color to load.
     * @return The loaded color.
     */
    private int getColor(@ColorRes int colorRes) {
        return ContextCompat.getColor(mActivity, colorRes);
    }

    static class ViewHolder extends RecyclerView.ViewHolder {

        private ItemCategoryBinding mBinding;

        public ViewHolder(ItemCategoryBinding binding) {
            super(binding.getRoot());
            mBinding = binding;
        }

        public ItemCategoryBinding getBinding() {
            return mBinding;
        }
    }
}