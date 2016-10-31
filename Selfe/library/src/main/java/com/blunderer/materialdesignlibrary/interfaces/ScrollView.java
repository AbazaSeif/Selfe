package com.blunderer.materialdesignlibrary.interfaces;

public interface ScrollView {

    void setRefreshing(boolean refreshing);

    int getContentView();

    boolean pullToRefreshEnabled();

    int[] getPullToRefreshColorResources();

    void onRefresh();

}
