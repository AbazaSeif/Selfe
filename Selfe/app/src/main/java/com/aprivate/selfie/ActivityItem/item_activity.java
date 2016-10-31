package com.aprivate.selfie.ActivityItem;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.content.ContextCompat;
import android.support.v4.view.ViewCompat;
import android.support.v4.view.ViewPropertyAnimatorListenerAdapter;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.util.Log;
import android.view.MenuItem;
import android.view.View;
import android.view.Window;
import android.view.animation.Interpolator;
import android.widget.FrameLayout;
import android.widget.TextView;
import android.widget.Toast;

import com.aprivate.selfie.Home.ApiLevelHelper;
import com.aprivate.selfie.Home.Category;
import com.aprivate.selfie.Home.Database;
import com.aprivate.selfie.R;
import com.github.rubensousa.floatingtoolbar.FloatingToolbar;
import com.mingle.entity.MenuEntity;
import com.mingle.sweetpick.DimEffect;
import com.mingle.sweetpick.RecyclerViewDelegate;
import com.mingle.sweetpick.SweetSheet;

import java.util.ArrayList;

public class item_activity extends AppCompatActivity implements FloatingToolbar.ItemClickListener,
        Toolbar.OnMenuItemClickListener, FloatingToolbar.MorphListener {
    private static final String TAG = "News";
    private static final String IMAGE_CATEGORY = "image_category_";
    private static final String DRAWABLE = "drawable";

    private Interpolator mInterpolator;
    private Category mCategory;
    private FloatingToolbar mFloatingToolbar;
    private FloatingActionButton fabbutton;
    private FrameLayout MainView;
    private View mToolbarBack;
    private boolean mShowingFromAppBar;
    private SweetSheet Sheet = null;

    @Override
    protected void onDestroy() {
        super.onDestroy();
        mFloatingToolbar.removeMorphListener(this);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_news);
        String categoryId = getIntent().getStringExtra(Category.TAG);




        populate(categoryId);

    }

    private void CreateSheetMenu() {
        Sheet = new SweetSheet(MainView);
        final ArrayList<MenuEntity> list = new ArrayList<>();
        MenuEntity menuEntity1 = new MenuEntity();

        menuEntity1.iconId = R.drawable.ic_markunread_black_24dp;
        menuEntity1.titleColor = mCategory.getTheme().getTextPrimaryColor();
        menuEntity1.title = "Messages";
        list.add(menuEntity1);

        menuEntity1 = new MenuEntity();
        menuEntity1.iconId = R.drawable.ic_facebook_box;
        menuEntity1.titleColor = mCategory.getTheme().getTextPrimaryColor();
        menuEntity1.title = "Facebook";
        list.add(menuEntity1);

        menuEntity1 = new MenuEntity();
        menuEntity1.iconId = R.drawable.ic_google_plus_box;
        menuEntity1.titleColor = mCategory.getTheme().getTextPrimaryColor();
        menuEntity1.title = "Google+";
        list.add(menuEntity1);


        Sheet.setBackgroundClickEnable(false);

//        Sheet.setMenuList(R.menu.main);
        Sheet.setMenuList(list);

        Sheet.setDelegate(new RecyclerViewDelegate(true));
//        Sheet.setDelegate(new ViewPagerDelegate());

        Sheet.setBackgroundEffect(new DimEffect(1.0f));

        Sheet.setOnMenuItemClickListener(new SweetSheet.OnMenuItemClickListener() {
            @Override
            public boolean onItemClick(int i, MenuEntity menuEntity) {
                ((RecyclerViewDelegate) Sheet.getDelegate()).notifyDataSetChanged();
                Toast.makeText(item_activity.this, "Select " + menuEntity.title + " in Position : " + i, Toast.LENGTH_SHORT).show();
                return true;
            }
        });


    }

    public static Intent getStartIntent(Activity activity, Category category) {
        Intent starter = new Intent(activity, item_activity.class);
        starter.putExtra(Category.TAG, category.getId());
        return starter;
    }

    @SuppressLint("NewApi")
    private void populate(String categoryId) {
        if (null == categoryId) {
            Log.w(TAG, "Didn't find a category. Finishing");
            finish();
        }
        Database database = new Database(this);
        mCategory = database.GetCategory(categoryId);
        if (mCategory != null) {
            setTheme(mCategory.getTheme().getStyleId());
            if (ApiLevelHelper.isAtLeast(Build.VERSION_CODES.LOLLIPOP)) {
                Window window = getWindow();
                window.setStatusBarColor(ContextCompat.getColor(this, mCategory.getTheme().getPrimaryDarkColor()));
            }
            initLayout(mCategory.getId());
            initToolbar(mCategory);
            CreateSheetMenu();
        } else {
            finish();
        }
    }

    private void initLayout(String categoryId) {
        setContentView(R.layout.activity_news);
        //noinspection PrivateResource
        MainView = (FrameLayout) findViewById(R.id.main_fragment_container);

        int resId = getResources().getIdentifier(IMAGE_CATEGORY + categoryId, DRAWABLE,
                getApplicationContext().getPackageName());
        MainView.setBackgroundResource(resId);
        ViewCompat.animate(MainView)
                .scaleX(1)
                .scaleY(1)
                .alpha(1)
                .setInterpolator(mInterpolator)
                .setStartDelay(300)
                .start();
        mFloatingToolbar = (FloatingToolbar) findViewById(R.id.floatingToolbar);
        fabbutton = (FloatingActionButton) findViewById(R.id.fab_button);
        mFloatingToolbar.setClickListener(this);
        mFloatingToolbar.addMorphListener(this);

        fabbutton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                mFloatingToolbar.attachFab(fabbutton);
                mFloatingToolbar.show();
                mShowingFromAppBar = true;
            }
        });


    }

    private void initToolbar(Category category) {
        mToolbarBack = findViewById(R.id.back);
        mToolbarBack.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                onBackPressed();
            }
        });
        TextView titleView = (TextView) findViewById(R.id.category_title);
        titleView.setText(category.getName());
        titleView.setTextColor(ContextCompat.getColor(this,
                category.getTheme().getTextPrimaryColor()));
    }

    @Override
    public void onBackPressed() {
        if (fabbutton == null) {
            // Skip the animation if icon or fab are not initialized.
            super.onBackPressed();
            return;
        }

        ViewCompat.animate(mToolbarBack)
                .scaleX(0f)
                .scaleY(0f)
                .alpha(0f)
                .setDuration(100)
                .start();

        // Scale the icon and fab to 0 size before calling onBackPressed if it exists.
        ViewCompat.animate(MainView)
                .scaleX(.7f)
                .scaleY(.7f)
                .alpha(0f)
                .setInterpolator(mInterpolator)
                .start();

        ViewCompat.animate(fabbutton)
                .scaleX(0f)
                .scaleY(0f)
                .setInterpolator(mInterpolator)
                .setStartDelay(100)
                .setListener(new ViewPropertyAnimatorListenerAdapter() {
                    @SuppressLint("NewApi")
                    @Override
                    public void onAnimationEnd(View view) {
                        if (isFinishing() ||
                                (ApiLevelHelper.isAtLeast(Build.VERSION_CODES.JELLY_BEAN_MR1)
                                        && isDestroyed())) {
                            return;
                        }
                        item_activity.super.onBackPressed();
                    }
                })
                .start();
    }


    @Override
    public void onItemClick(MenuItem menuItem) {
        Toast.makeText(this, "Click " + menuItem.getTitle(), Toast.LENGTH_SHORT).show();
        if (Sheet.isShow()) {
            Sheet.dismiss();
        } else {
            Sheet.toggle();
        }
    }

    @Override
    public void onItemLongClick(MenuItem menuItem) {
        Toast.makeText(this, "Long " + menuItem.getTitle(), Toast.LENGTH_SHORT).show();
    }

    @Override
    public void onMorphEnd() {

    }

    @Override
    public void onMorphStart() {

    }

    @Override
    public void onUnmorphStart() {

    }

    @Override
    public void onUnmorphEnd() {
        if (mShowingFromAppBar) {
            fabbutton.show();
        }


        mShowingFromAppBar = false;
    }

    @Override
    public boolean onMenuItemClick(MenuItem item) {
        return false;
    }
}
