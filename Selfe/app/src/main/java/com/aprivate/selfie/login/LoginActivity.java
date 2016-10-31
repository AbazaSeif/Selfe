package com.aprivate.selfie.login;

import android.animation.ObjectAnimator;
import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v7.widget.CardView;
import android.util.Log;
import android.view.View;
import android.widget.TextView;
import android.widget.Toast;

import com.aprivate.selfie.Home.NivergatorAcount;
import com.aprivate.selfie.R;
import com.aprivate.selfie.wedget.custom.AbstractActivity;
import com.aprivate.selfie.wedget.custom.CustomEdittext;
import com.aprivate.selfie.wedget.custom.ProgressGenerator;
import com.dd.processbutton.iml.ActionProcessButton;
import com.github.ppamorim.dragger.DraggerPosition;
import com.github.ppamorim.dragger.DraggerView;

import butterknife.Bind;

public class LoginActivity extends AbstractActivity {
    private static final String TAG = "LoginActivity";
    private static final int REQUEST_SIGNUP = 0;
    public static final String DRAG_POSITION = "drag_position";


    @Bind(R.id.dragger_view)
    DraggerView draggerView;
    CustomEdittext _emailText;
    CustomEdittext _passwordText;
    TextView Linksingup;
    ProgressGenerator progressGenerator = null;
    ActionProcessButton btnSinin;
    CardView logCard;
    int translateY;

    @Override
    protected void onPostCreate(@Nullable Bundle savedInstanceState) {
        super.onPostCreate(savedInstanceState);
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        draggerView.setDraggerPosition((DraggerPosition) getIntent().getSerializableExtra(DRAG_POSITION));
        progressGenerator = new ProgressGenerator();
        logCard = (CardView)findViewById(R.id.wdCard);
        _emailText = (CustomEdittext) findViewById(R.id.input_email);
        _passwordText = (CustomEdittext) findViewById(R.id.input_password);
        Linksingup = (TextView) findViewById(R.id.link_signup);
        btnSinin = (ActionProcessButton) findViewById(R.id.btnSignIn);
        Linksingup.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // Start the Signup activity
                startDraggerActivity(DraggerPosition.RIGHT);
            }
        });

        translateY = logCard.getScrollY();

        btnSinin.setMode(ActionProcessButton.Mode.ENDLESS);
        btnSinin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                ButtonSiginStatus(true);
                if (validate()) {
                    login();
                } else {
                    ButtonSiginStatus(false);
                    onLoginFailed();
                }
            }
        });

    }


    @Override
    protected int getContentViewId() {
        return R.layout.activity_login;
    }

    public void login() {
        Log.d(TAG, "Login");

        if (!validate()) {
            onLoginFailed();
            return;
        }

        ObjectAnimator animator = ObjectAnimator.ofFloat(logCard, "translationY", 0, 950, -translateY);
        animator.setDuration(500).start();

        final String email = _emailText.getText().toString();
        String password = _passwordText.getText().toString();

        // TODO: Implement your own authentication logic here.
        new android.os.Handler().postDelayed(
                new Runnable() {
                    public void run() {
                        onLoginSuccess(email);
                    }
                }, 1000);
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == REQUEST_SIGNUP) {
            if (resultCode == RESULT_OK) {
                onLoginSuccess(data.getStringExtra("email"));
            }
        }
    }

    public void onLoginSuccess(String Email) {
        Intent Home = new Intent(this, NivergatorAcount.class);
        Home.putExtra("Email", Email);
        Home.putExtra("Name", "My Name");
        Home.putExtra("Money", "0 Coin");
        startActivity(Home);
        finish();
    }

    public void onLoginFailed() {
        Toast.makeText(getBaseContext(), "Login failed", Toast.LENGTH_LONG).show();
    }

    public boolean validate() {
        boolean valid = true;

        String email = _emailText.getText().toString();
        String password = _passwordText.getText().toString();

        if (email.isEmpty() || !android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            _emailText.setError("enter a valid email address");
            valid = false;
            btnSinin.setText(getString(R.string.Sign_in));
            logCard.setCardBackgroundColor(Color.RED);
        } else {
            _emailText.setError(null);
            logCard.setCardBackgroundColor(Color.WHITE);
        }

        if (password.isEmpty() || password.length() < 4 || password.length() > 20) {
            _passwordText.setError("between 4 and 20 alphanumeric characters");
            valid = false;
            btnSinin.setText(getString(R.string.Sign_in));
            logCard.setCardBackgroundColor(Color.RED);
        } else {
            _passwordText.setError(null);
            logCard.setCardBackgroundColor(Color.WHITE);
        }

        return valid;
    }

    private void ButtonSiginStatus(boolean mood) {
        if (mood) {
            btnSinin.setText(getString(R.string.Loading));
            btnSinin.setProgress(0);
            progressGenerator.start(btnSinin);
            btnSinin.setEnabled(false);

        } else {
            btnSinin.setErrorText(getString(R.string.Failed));
            btnSinin.setProgress(-1);
            progressGenerator.Error(btnSinin);
            btnSinin.setEnabled(true);
        }
    }

    private void startDraggerActivity(DraggerPosition dragPosition) {
        Intent intent = new Intent(this, signup_drp.class);
        intent.putExtra(signup_drp.DRAG_POSITION, dragPosition);
        startActivityNoAnimation(intent);
    }

    private void startActivityNoAnimation(Intent intent) {
        intent.setFlags(Intent.FLAG_ACTIVITY_NO_ANIMATION);
        startActivity(intent);
    }

}
